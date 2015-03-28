<?php
/*
* Copyright (c) Vinicius Gerevini, distributed
* as-is and without warranty under the MIT License.
* See http://opensource.org/licenses/MIT for more information.
* This information must remain intact.
*/
    
    require_once('../../common.php');
    require_once('class.compass.php');
    require_once('class.console.php');
    checkSession();
    
    $logFileName = './data/'.$_SESSION["user"].'_data.log';
    if(!file_exists($logFileName)) {
        file_put_contents($logFileName);
    }
    
    $compass = new Compass($logFileName);
    
    switch($_GET['action']) {
        //=========
        // Settings
        //=========
        case 'save-settings':
            if (isset($_POST['settings'])) {
                saveJSON("codiadCustom.settings.php", json_decode($_POST['settings']), "config");
                echo '{"status":"success","message":"Settings saved"}';
            } else {
                echo '{"status":"error","message":"Missing parameter"}';
            }
            break;
            
        case 'load-settings':
            if (file_exists(DATA."/config/codiadCustom.settings.php")) {
                echo json_encode(getJSON("codiadCustom.settings.php", "config"));
            } else {
                echo file_get_contents("./data/settings.json");
            }
            break;
            
        //=========
        // Logs
        //=========
        case 'get-log':
            if(isset($_GET['lines'])) {
                $console = new Console($logFileName);
            	echo '{"status":"success","message": '.$console->getLog($_GET['lines']).'}';
        	} else {
        		echo '{"status":"error","message":"Missing parameter"}';	
        	}
            break;
            
        case 'clear-log':
            $console = new Console($logFileName);
            $console->clearLog();
            echo '{"status":"success","message": ""}';
            break;
            
        case 'get-last-execution-time':
            $compass->touch();
            $console = new Console($logFileName);
            echo '{"status":"success","message": '.$console->lastExecutionTime().' }';
            break;
            
        //=========
        // Commands
        //=========
        case 'create':
            if(isset($_GET['path'])) {
            	echo $compass->create(getRelativePath($_GET['path']));
        	} else {
        		echo '{"status":"error","message":"Missing parameter"}';	
        	}
            break;
           
        case 'compile':
            if(isset($_GET['path'])) {
            	echo $compass->compile(getRelativePath($_GET['path']));
        	} else {
        		echo '{"status":"error","message":"Missing parameter"}';	
        	}
            break;
        
        case 'clean':
            if(isset($_GET['path'])) {
            	echo $compass->clean(getRelativePath($_GET['path']));
        	} else {
        		echo '{"status":"error","message":"Missing parameter"}';	
        	}
            break;
            
        case 'watch':
        	if(isset($_GET['path'])) {
            	echo $compass->watch(getRelativePath($_GET['path']));
        	} else {
        		echo '{"status":"error","message":"Missing parameter"}';	
        	}
            break;
        
        case 'stop-watch':
            echo $compass->stopWatch();
            break;
        //=========
        // Support
        //=========
        case 'has-config':
            if(isset($_GET['path'])) {
            	if(file_exists(getRelativePath($_GET['path']).'/config.rb')){
            	    echo '{"status":"success","hasConfig": true}';
            	} else {
            	    echo '{"status":"success","hasConfig": flase}';
            	}
        	} else {
        		echo '{"status":"error","message":"Missing parameter"}';	
        	}
            break;
            
        case 'is-installed':
            echo $compass->isInstalled();
            break;
            
        case 'is-running':
            echo '{"status":"success","message": '.json_encode($compass->isRunning()).' }';
            break;
            
        default:
            echo '{"status":"error","message":""}';
            break;
    }

    function getRelativePath($path) {
        return "../../workspace/".$path;
    }

?>
