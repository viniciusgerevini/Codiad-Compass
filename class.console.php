<?php
    class Console {
        
        private $logFile;
        
        function __construct($logFile) {
            $this->logFile = $logFile;
        }
        
        public function lastExecutionTime() {
            $number = 20;
            $lines = file($this->logFile);
            $return = array();
            
            $end = count($lines) -1;
            
            if($number > $end)
                $number = $end;
            
            $lastTime = "";
            while($end >= 0) {
                if(preg_match('/(.*)(Change detected)(.*)/', $lines[$end])) {
                    preg_match('/(?:.*)(?:Change detected at )(([0-9]|\:)+)(?:.*)/', $lines[$end], $matches);
                    $lastTime = $matches[1];
                    break;
                }
                $end--;
            }
            
            return json_encode($lastTime);
        }
        
        public function getLog($number) {
            $lines = file($this->logFile);
            $return = array();
            
            if($number > count($lines))
                $number = count($lines);
            
            for ($i = count($lines) - $number; $i < count($lines); $i++) {
                $return[] = $this->convertShellColors($lines[$i]);
            }
            return json_encode($this->removeRelativeWorkspacePath(implode("<br/>", $return)));
        }
        
        public function clearLog() {
            file_put_contents($this->logFile, "");
        }
        
        private function convertShellColors($string) {
            
            $string = preg_replace('/\033/', '', $string); // Remove ESC ascii char
            
            $string = preg_replace('/(\[30m)((\w|\s)*)(\[0m)/','<span class="compass-color-black">${2}</span>', $string);
            $string = preg_replace('/(\[31m)((\w|\s)*)(\[0m)/','<span class="compass-color-red">${2}</span>', $string);
            $string = preg_replace('/(\[32m)((\w|\s)*)(\[0m)/','<span class="compass-color-green">${2}</span>', $string);
            $string = preg_replace('/(\[33m)((\w|\s)*)(\[0m)/','<span class="compass-color-yellow">${2}</span>', $string);
            $string = preg_replace('/(\[34m)((\w|\s)*)(\[0m)/','<span class="compass-color-blue">${2}</span>', $string);
            $string = preg_replace('/(\[35m)((\w|\s)*)(\[0m)/','<span class="compass-color-magenta">${2}</span>', $string);
            $string = preg_replace('/(\[36m)((\w|\s)*)(\[0m)/','<span class="compass-color-cyan">${2}</span>', $string);
            $string = preg_replace('/(\[37m)((\w|\s)*)(\[0m)/','<span class="compass-color-white">${2}</span>', $string);
            $string = preg_replace('/(\[40m)((\w|\s)*)(\[0m)/','<span class="compass-color-black compass-text-bold">${2}</span>', $string);
            $string = preg_replace('/(\[41m)((\w|\s)*)(\[0m)/','<span class="compass-color-red compass-text-bold">${2}</span>', $string);
            $string = preg_replace('/(\[42m)((\w|\s)*)(\[0m)/','<span class="compass-color-green compass-text-bold">${2}</span>', $string);
            $string = preg_replace('/(\[43m)((\w|\s)*)(\[0m)/','<span class="compass-color-yellow compass-text-bold">${2}</span>', $string);
            $string = preg_replace('/(\[44m)((\w|\s)*)(\[0m)/','<span class="compass-color-blue compass-text-bold">${2}</span>', $string);
            $string = preg_replace('/(\[45m)((\w|\s)*)(\[0m)/','<span class="compass-color-magenta compass-text-bold">${2}</span>', $string);
            $string = preg_replace('/(\[46m)((\w|\s)*)(\[0m)/','<span class="compass-color-cyan compass-text-bold">${2}</span>', $string);
            $string = preg_replace('/(\[47m)((\w|\s)*)(\[0m)/','<span class="compass-color-white compass-text-bold">${2}</span>', $string);
            $string = preg_replace('/(\[1m)((\w|\s)*)(\[0m)/','<span class="compass-text-bold">${2}</span>', $string);
            $string = preg_replace('/(\[3m)((\w|\s)*)(\[0m)/','<span class="compass-text-italic">${2}</span>', $string);
            $string = preg_replace('/(\[4m)((\w|\s)*)(\[0m)/','<span class="compass-text-blink">${2}</span>', $string);
            $string = preg_replace('/(\[5m)((\w|\s)*)(\[0m)/','<span class="compass-text-negative">${2}</span>', $string);
            return $string;
        }
        
        private function removeRelativeWorkspacePath($string) {
            return str_replace("../../workspace/", "", $string);
        }
        
        
        
    }
?>