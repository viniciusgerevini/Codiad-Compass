<?php

    class Compass {
        private $outputFile;
        
        function __construct($outputFile) {
            $this->outputFile = $outputFile;
        }
        
        public function create($path) {
            if(!$this->run("compass create '".$path."'"))
            	return '{"status":"success","message": "compass project created"}';
            else
            	return '{"status":"error","message":"error"}';
        }
        
        public function compile($path) {
            if(!$this->run("compass compile '".$path."'"))
            	return '{"status":"success","message": "'.$path.' compiled"}';
            else
            	return '{"status":"error","message":"error compiling"}';
        }
        
        public function watch($path) {
            
            $pid = shell_exec('compass watch "'.$path.'" > "'.$this->outputFile.'" 2>&1 & echo $!');
            
            $this->setPid($pid);
            
            if($pid)
            	return '{"status":"success","message":"compass watching" }';
            else
            	return '{"status":"error","message":"compass watching failed"}';
        }
        
        public function stopWatch() {
            if(!$this->isRunning())
                return '{"status":"success","message":"compass watch is already stopped"}';
                
            if(!$this->run("kill -9 ".$this->getPid()))
            	return '{"status":"success","message":"compass watch stopped"}';
            else
            	return '{"status":"error","message":"error while stopping compass"}';
        }
        
        public function clean() {
            if(!$this->run('compass clean "'.$path.'"'))
            	return '{"status":"success","message": "project cleanned"}';
            else
            	return '{"status":"error","message":"error"}';
        }
        
        public function isRunning() {
        	try {
                $result = shell_exec(sprintf('ps %d', $this->getPid()));
                if(count(preg_split("/\n/", $result)) > 2) {
                    return true;
                }
            } catch(Exception $e) {}

            return false;
        }
        
        public function isInstalled() {
        	if(!$this->run("compass version "))
            	return '{"status":"success","message": "compass installed"}';
            else
            	return '{"status":"error","message":"compass not installed"}';
        }
        
        private function run($cmd) {
        	exec($cmd, $output, $returnCode);
        	return $returnCode;
        }
        
        private function setPid($pid) {
            $_SESSION['codiadCompassWatchPid'] = $pid;
        }
        
        private function getPid() {
            return $_SESSION['codiadCompassWatchPid'];
        }
    }
    
?>