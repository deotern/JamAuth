<?php
namespace JamAuth\Importer;

class SimpleAuthYAML extends DataImporter{
    
    public function read(){
        $dir = rtrim(getcwd(), DIRECTORY_SEPARATOR)."/plugins/SimpleAuth/players/";
        if(!is_dir($dir)){
            $this->plugin->sendInfo("SimpleAuth Data Missing");
            return false;
	}
        $this->setTotal(iterator_count(new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS)));
        $this->plugin->getDatabase()->truncate();
        //Indexing
        $this->plugin->sendInfo($this->plugin->getTranslator()->translate(
                "import.start",
                [$this->getReaderName().":".$this->getReaderType()]
        ));
        $i = 0;
        foreach(glob($dir."*", GLOB_ONLYDIR) as $dir){
            $names = glob($dir."/"."*.{yml}", GLOB_BRACE);
            foreach($names as $name){
                $i++;
                $yaml = yaml_parse_file($name);
                $data["name"] = basename($name);
                $data["food"] = $yaml["hash"];
                $data["time"] = $yaml["registerdate"];
                $this->write($data);
                $this->setPace($i);
            }
        }
        $this->plugin->sendInfo($this->plugin->getTranslator()->translate("import.end"));
    }
    
    public function getReaderName(){
        return "SimpleAuth";
    }
    
    public function getReaderType(){
        return "YAML";
    }
}