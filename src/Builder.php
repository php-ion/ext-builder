<?php

namespace ION\Ext;


use ION\Ext\Builder\CompileBuilder;
use ION\Ext\Builder\DockerBuilder;

class Builder
{

    const MODE_DOCKER = "docker";
    const MODE_LOCAL  = "local";

    public $repo = 'php-ion/builds';

    /**
     * @var array
     */
    public $config = [];
    public $readme_header = "";
    public $dry_run = false;
    public $version = false;

    public function __construct()
    {
        chdir(dirname(__DIR__));
        $this->config = require(__DIR__.'/../config/config.php');

        $this->dry_run = $this->hasOption("dry-run");
        $this->version = $this->getOption("build");
    }

    /**
     * Checks whether the parameter
     *
     * @param string $long
     *
     * @return bool
     *
     */
    public function hasOption(string $long) {
        $options = getopt("", [$long]);
        return isset($options[ $long ]);
    }

    /**
     * @param string $long
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption(string $long, $default = null) {
        $options = getopt("", [$long."::"]);
        if(isset($options[ $long ])) {
            return $options[ $long ];
        } else {
            return $default;
        }
    }

    public function printHelp() {
        $cwd = getcwd();
        echo <<<HELP
Usage: builder --build=VERSION [commands]
            
    --build=VERSION      Build ION version (see tags)
    --path=PATH          the path, where extensions will be stored (by default $cwd)
    --rebuild            rebuild already existed extensions
    --ignore             ignore docker fails
    --mask=GLOB          the glob-mask of extensions to be built or rebuilt 
    --dry-run            run without operations, just print commands
    
HELP;
    }


    public function run() {
        chdir(dirname(__DIR__));
        if ($this->hasOption("help")) {
            $this->printHelp();
            return;
        }
        if ($this->hasOption("path")) {
            $path = realpath($this->getOption("path"));
            if(!$path) {
                throw new \RuntimeException("No path");
            }
        } else {
            $path = getcwd();
        }

        if ($this->hasOption("build")) {
            $this->build($path);
        } else if ($this->hasOption("reindex")) {
            $this->indexer($path);
        }
    }

    public function build(string $path) {
        if(!$this->version) {
            throw new \RuntimeException("No --build present");
        }
        $mask   = $this->getOption("mask");
        $force  = $this->hasOption("force");
        foreach($this->config["builds"] as $os => $info) {
            $variants = iterator_to_array(self::combination($info["matrix"]));
            $this->write("Total ".count($variants)." variants for $os...");
            if ($info["mode"] == self::MODE_DOCKER) {
                $builder = new DockerBuilder($this, $info["docker_os"], "16.04");
            } else {
                $builder = new CompileBuilder($this, $info["build_path"]);
            }
            foreach ($variants as $id => $vars) {
                $target_rel = "{$this->version}/{$os}/" . implode("_", array_keys($vars)) . ".so";
                $target = "{$path}/{$target_rel}";
                if ($mask && fnmatch($mask, $target_rel) == false) {
                    $this->write("SKIP: Target $target_rel do not match by mask (use --force). Skip build.");
                    continue;
                }
                if (file_exists($target) && !$force) {
                    $this->write("SKIP: Target $target already exists. Skip build.");
                    continue;
                }

                $this->write("\nBUILD ".implode(" ", array_keys($vars)));
                $builder->build($vars + ["ion{$this->version}" => "ION_RELEASE='{$this->version}'"]);

                if (!file_exists(dirname($target))) {
                    mkdir(dirname($target), 0755, true)  ;
                }
                $builder->copyTo($target);
                $builder->cleanup();
            }
        }
        $this->indexer($path);
    }

    /**
     * @param string $path
     */
    public function indexer(string $path) {
        $index = [];
        $list  = [];
        $it = new \RecursiveDirectoryIterator($path,
            \RecursiveDirectoryIterator::SKIP_DOTS
            | \FilesystemIterator::CURRENT_AS_PATHNAME
            | \FilesystemIterator::KEY_AS_FILENAME);
        $iterator = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($iterator as $ext) {
            $relative = str_replace($path . "/", "", $ext);
            if (in_array($relative, ["index.json", "readme.md"])) {
                continue;
            }
            if(!preg_match(
                '#^(?<version>.*?)/(?<os_group>\w+)/(?<os>.*?)_php-(?<php_version>\d+\.\d+)_(?<debug>.*?)_(?<zts>.*?)\.so$#Su',
                $relative,
                $matches
            )) {
                throw new \RuntimeException("Can't parse $relative");
            }
            if (strpos($matches["version"], ".")) {
                $version_int = intval(sprintf("%'.02d%'.02d%'.02d", ...explode(".", $matches["version"])));
            } else {
                $version_int = 0;
            }
            $index[$matches["version"]][basename($relative)] = [
                "path" => $relative,
                "version" => $matches["version"],
                "version_int" => $version_int,
                "os_group" => $matches["os_group"],
                "build_os" => $matches["os"],
                "php_version" => $matches["php_version"],
                "debug" => $matches["debug"] == "debug",
                "zts" => $matches["zts"] == "zts",
                "size" => filesize($path."/".$relative),
                "build_time" => filemtime($path."/".$relative)

            ];

            $list[ $matches["version"] ][ $matches["os_group"] ][]
                = "[".basename($relative)."](./$relative?raw=true) _("
                . round(filesize($path."/".$relative)/1024/1024, 1)." MiB, "
                . gmdate("Y-m-d H:i:s", filemtime($path."/".$relative))
                .")_";
        }
        arsort($list);
        arsort($index);
        $readme = ["List of PHP-ION builds\n===\n"];
        foreach ($list as $version => $os) {
            $readme[] = "# $version\n";
            foreach ($os as $os_name => $links) {
                $readme[] = "## $os_name\n\n* ".implode("\n* ", $links)."\n";
            }
        }

        file_put_contents("{$path}/readme.md", implode("\n", $readme));
        file_put_contents("{$path}/index.json", json_encode([
            "date"     => gmdate("Y-m-d H:i:s"),
            "os"       => $this->config["os_map"],
            "variants" => $index
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function error($msg) {
        fwrite(STDERR, "ERROR: " . $msg."\n");
    }

    public function write($msg) {
        fwrite(STDERR, $msg . "\n");
    }

    public function exec($cmd, bool $return = false) {
        $this->write("EXEC: " . $cmd);
        if ($this->dry_run) {
            return [];
        }
        if($return) {
            exec($cmd, $output, $code);
        } else {
            $output = [];
            passthru($cmd, $code);
        }
        if($code) {
            throw new \RuntimeException("Command $cmd failed");
        }

        return $output;
    }

    public static function combination($arrays) : \Generator
    {
        $array = current($arrays);
        $array_name = key($arrays);
        if(!$array) {
            throw new \RuntimeException("Vector can not be empty");
        }
        $has_more = next($arrays) !== false;
        foreach($array as $key => $value) {
            $name = is_numeric($key) ? $value : $key;
            if($has_more) {
                foreach(self::combination($arrays) as $k => $v) {
//                    $v[$key] = "$array_name=" . escapeshellarg($value);
                    yield $name . "/$k" => [$key => "$array_name=" . escapeshellarg($value)] + $v;
                }
            } else {
                yield $name => [$key => "$array_name=" . escapeshellarg($value)];
            }
        }
    }
}