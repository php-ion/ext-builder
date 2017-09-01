<?php



$builder = new Builder();

$builder->run();


class Builder
{
    public $repo = 'php-ion/builds';
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

    public function run() {
        chdir(dirname(__DIR__));
        if (!$this->hasOption("ion-version")) {
            throw new RuntimeException("No parameter '--ion-version=X.Y.Z'");
        }

        if ($this->hasOption("path")) {
            $path = realpath($this->getOption("path"));
            if(!$path) {
                throw new RuntimeException("No path");
            }
        } else {
            $path = getcwd();
        }

        $ion_version = $this->getOption("ion-version");
        $config = require('config/config.php');
        $readme = file_get_contents('resources/readme.header.md');
        foreach ($config["matrix"] as $os => $matrix) {
            if(!file_exists("docker/$os")) {
                throw new RuntimeException("Dockerfile for $os not found");
            }
            $readme .= "\n## ".ucfirst($os)."\n\n";
            $images = iterator_to_array(self::combination($matrix));
            $this->write("Begin build ".count($images)." images...");
            foreach ($images as $image_path => $args) {
                $this->write("\nINFO: Build image $image_path");
                $image_path = $os."/".$image_path;
                $image_id = str_replace("/","-", $image_path);
                $this->exec("docker build --force-rm --no-cache "
                    . "--tag=ion-ext:$image_id "
                    . "--build-arg " . implode(" --build-arg ", $args) . " "
                    . "--build-arg ION_RELEASE='$ion_version' docker/$os");

                $this->exec("docker create --name='ion-$image_id' ion-ext:$image_id");
                @mkdir($path."/".$image_path, 0777, true);
                $this->exec("docker cp ion-$image_id:/usr/src/ion.so - > $path/$image_path/ion-{$ion_version}.so");
                $this->exec("docker rm -v ion-$image_id");

                $readme .= " * [$image_path/ion-{$ion_version}.so](https://raw.githubusercontent.com/php-ion/builds/master/$image_path/ion-{$ion_version}.so)"
                    ." (".round(filesize("$path/$image_path/ion-{$ion_version}.so")/1024)." KiB)\n";
            }

            $readme .= "\n---\n*Build date: ".gmdate("Y-m-d H:i:s") . " GMT*";
        }
    }

    public function error($msg) {
        fwrite(STDERR, "ERROR: " . $msg."\n");
    }

    public function write($msg) {
        fwrite(STDERR, $msg . "\n");
    }

    public function exec($cmd) {
        $this->write("EXEC: " . $cmd);
//        passthru($cmd, $code);
//        if($code) {
//            throw new RuntimeException("Command $cmd failed");
//        }
    }

    public static function combination($arrays) : Generator
    {
        $array = current($arrays);
        $array_name = key($arrays);
        if(!$array) {
            throw new RuntimeException("Vector can not be empty");
        }
        $has_more = next($arrays) !== false;
        foreach($array as $key => $value) {
            $name = is_numeric($key) ? $value : $key;
            if($has_more) {
                foreach(self::combination($arrays) as $k => $v) {
                    array_unshift($v, "$array_name=" . escapeshellarg($value));
                    yield $name . "/$k" => $v;
                }
            } else {
                yield $name => ["$array_name=" . escapeshellarg($value)];
            }
        }
    }
}