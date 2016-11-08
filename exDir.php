<?php
/*
* exDir. Extension(aggregated) of Dir php class by adding some dir and files common functions.
*
* copyright virgilio Lino 2011 its opensource guys, so you are invited to contribute
* @author  Virgilio Lino
*
*/
class exDir 
{
  private $dir; // the php dir class
  private $lastFile;  //the pointer to the file we're reading
  private $fsDir;  //the pointer to the dir we're working on

/* constructor.
* Instantiate a dir object, if path=true and it doesnt exist, creates the dir.
* @param $path	The directory to open
* @param $touch   Optional, if true the class will rebuild the directory tree if not found
* @param $fsdir   optional, the absolute filesystem path, in standard home linux it's /var/www
*
*/
  public function __construct($path,$touch=false,$fsDir='/var/www/') {
    $this->fsDir=$fsDir;
    $newPath=str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR,$this->fsDir.$path);;
    if(!file_exists($newPath)&&$touch)$this->touchRecursive($path);      
    return($this->dir=dir($newPath));
  }

/* 
* Check every directory in the directory tree if not found, create it by calling touch
* i.e. When /a/b/c check /a if not found create it, check /a/b if not found create it
* @param $path	The directory to open
* @param $touch   Optional, if true the class will rebuild the directory tree if not found
* @param $fsdir   optional, the absolute filesystem path, in standard home linux its /var/www
*/


public function touchRecursive($path) {
    $pos=0;$prevPos=0;
    while($pos=strpos("|".$path,DIRECTORY_SEPARATOR,$prevPos)) {
      $this->touch(substr($path,$fsLen,$pos-$fsLen));
      $prevPos=$pos+1;
    }
    $this->touch($path);
   return $this;
  }

/* 
* Check $path directory if not found, create it. 
*
* @param $path	The directory to open/create
*
*/
  public function touch($path) {
    $path=$this->fsDir().$path;
    if(!is_dir($path))  {
      $oldumask = umask(0);
      mkdir($path,0777);
      umask($oldumask);
    }
  }

/*
* destuctor, just call the close method
*
*/  
public function __desctruct($dir='') {
    $this->close();
}

/*
* Open a directory. 
* 
* @param $path   If setted, open it, if not, open lastfile
*/  

public function cdin($path='') {
    if($path) {
      $this->dir=dir($path);
    } else {
      $this->dir=dir($this->lastFile);
    }
    return $this;
  }

/*
*
* Move lastFile, the files directory pointer to the next File
* 
*/  
  public function read() {
    $this->lastFile=$this->dir->read();
    while($this->lastFile=='.'||$this->lastFile=='..')$this->lastFile=$this->dir->read();
    return $this->lastFile;
  }

/*
*
*  Go up in the directory tree, 
*
*/
  public function cdup() {
    $lastSep=strrpos($this->path(),DIRECTORY_SEPARATOR);
    if(strlen($this->path())-$lastSep==1)  {
      $lastSep=strrpos($this->path(),DIRECTORY_SEPARATOR,-2);
    }
    $this->dir=dir(substr($this->path(),0,$lastSep));
    return $this;
  }

/*
*
*   Check for the current path(lastFile) or optional param $path wether if correpond to a file
*
*	@param path   optional, if false, the method work on lastFile attribute, if set, it's a path, we're going to check
*/ 
  public function isFile($path=false) {
    if($path) {
      return is_file($this->fsDir.DIRECTORY_SEPARATOR.$this->lastFile);     
    } else {
      return is_file($this->lastFile);
    }
  }

/*
*
*   Check for the current path($lastfile) if correspond to a directory
*/
  public function isDir() {
    return ($this->lastFile!='.'&&$this->lastFile!='..'&&is_dir($this->lastFile));
  }

/*
*
*   Check for the current path($lastfile) if correspond to a link
*/
  public function isLink() {
    return is_link($this->lastFile);
  }

/*
*
*    Return the content of the directory pointer
*/
  public function lastFile() {
    return $this->lastFile;
  }

  public function path() {
    return $this->dir->path;
  }

  public function serverPath() {
    return(substr($this->dir->path,strlen($this->fsDir)));
  }

  public function setFSDir($path) {
    $this->fsDir=$path;
  }

  
  public function fsDir() {
    return $this->fsDir;
  }

  public function is() {
    return is_dir($this->path()) ||
           is_file($this->path());
  }
  public function search($path=false,$multiSearch=false,$exclude=false,$exts=false) {
    $this->files=false;
    $trovato=false;
    if($exts) {
      $lPath=$this->path().$path.$ext;
      if(is_array($exts)) {
	foreach($exts as $ext) {
	  //echo "<BR>cerco ".$lPath.$ext."---";
          if(is_file($lPath.$ext)) {
	    $trovato=$lPath.$ext;
	    break;
	  } 
	}
      } else {
        //echo "<BR>cerco ".$lPath.$ext."---";
        if(is_file($lPath.$ext)){ $trovato=$lPath.$ext;}
      }
    }
    if($trovato) {
      $this->files[]=$trovato;
    } else {
      $lastSep=strrpos($path,DIRECTORY_SEPARATOR);
      $path=substr($path,$lastSep);
      //echo "CINGOLO:".$path;
      $this->dir->rewind();
      if($multiSearch) {
	while(false!==$this->read()) {
	  if(!$exclude&&(!$path||strpos("|".$this->lastFile,$path)>0)) {
	    $this->files[]=$this->lastFile;	
	  } elseif(strpos("|".$this->lastFile,$exclude)<1&&strpos("|".$this->lastFile,$path)>0) {
	    $this->files[]=$this->lastFile;	
	  }
	}
      } else {
	if($exclude) {
	  while(false!==$this->read()&&(strpos("|".$this->lastFile,$path)<1||(strpos("|".$this->lastFile,$exclude)>0))) {;}
	  if(strpos("|".$this->lastFile,$path)>0) {
	    $this->files[]=$this->lastFile;
	  }
	} else {
	  while(false!==$this->read()&&strpos("|".$this->lastFile,$path)<1) {;}
	  if(strpos("|".$this->lastFile,$path)>0) {
	    $this->files[]=$this->lastFile;
	  }
	}
      }
    }
    return $this;
  }


  public function visit($fileSystem=false,$searchFor=false,$exclude=false) {
    $this->dir->rewind();
    $files=false;
    if($fileSystem) {
      while(false!==$this->read()) {
        if($searchFor) {
	  if(strpos("|".$this->lastFile,$searchFor)>0) {
	    if($exclude) {
	      if(strpos("|".$this->lastFile,$exclude)<1)$files[]=$this->dir->path.DIRECTORY_SEPARATOR.$this->lastFile;
	    } else {
	      $files[]=$this->dir->path.DIRECTORY_SEPARATOR.$this->lastFile;
	    }
	  }
	} else {
	  if($exclude) {
	    if(strpos("|".$this->lastFile,$exclude)<1)$files[]=$this->dir->path.DIRECTORY_SEPARATOR.$this->lastFile;
	  } else {
	    $files[]=$this->dir->path.DIRECTORY_SEPARATOR.$this->lastFile;
	  }
	}
      }
    } else {
      $subPath=substr($this->dir->path,strlen($this->fsDir()));
      while(false!==$this->read()) {
	if($searchFor) {
	  if(strpos("|".$this->lastFile,$searchFor)>0) {
	    if($exclude) {
	      if(strpos("|".$this->lastFile,$exclude)<1)$files[]=$subPath.$this->lastFile;
	    } else {
	      $files[]=$subPath.DIRECTORY_SEPARATOR.$this->lastFile;
	    }
	  }
	} else {
	  if($exclude) {
	    if(strpos("|".$this->lastFile,$exclude)<1)$files[]=$subPath.$this->lastFile;
	  } else {
	    $files[]=$subPath.DIRECTORY_SEPARATOR.$this->lastFile;
	  }
	}
      }
    } 
   return $files;
  }

  function searchDeep($path, $pattern) {
    $handle = opendir($path);
    while (false !== ($directory = readdir($handle))) {
      if(($directory!='.')&&($directory!='..')) {
	if(is_dir($path.'/'.$directory)) {
	  $risposta=false;
	  $risposta=searchDeep($path.'/'.$directory,$pattern);
	  if($risposta)return($risposta);
	} elseif(substr($directory,0,strlen($directory)-4)==$pattern)   {
	  return($path);
	}
      }
    }
    return false;
  }


  private function dirOperation($op="copy",$toPath) {
    $toDir=new exDir(str_replace(" ","-",strtolower(transcribe($toPath))),true);
    $files=$this->search('.',true)->files;
    if(is_array($files)) {
      //echo $this->path()."-".$toDir->path();
      foreach($files as $file) {
	//copy($this->path().$file,$toDir->path());
	$lastSep=strrpos($file,DIRECTORY_SEPARATOR);
	$justFile=str_replace(" ","-",strtolower(transcribe(substr($file,$lastSep))));
        //echo " \n copia da ".$this->path().DIRECTORY_SEPARATOR.$file." a ".$toDir->path().$justFile." \n ";
	$op($this->path().DIRECTORY_SEPARATOR.$file,$toDir->path().$justFile);
      }
    }
    return $this;
  }

  private function fileOperation($op="copy",$toPath=false,$fromFile=false,$newFileName=false,$preserveExtension=false) {
    if(!$toPath)$toPath=$this->path();
    if(!$fromFile)$fromFile=$this->path().DIRECTORY_SEPARATOR.$this->lastFile;     
    $lastSep=strrpos($fromFile,DIRECTORY_SEPARATOR)+1;
    $justFile=substr($fromFile,$lastSep);
    $ext='';
    if($preserveExtension) {
      $postExt=strrpos($justFile,'.');
      $ext=substr($justFile,$postExt);
      $justFile=substr($justFile,0,$postExt);     
    }
    //$fromFile=$fromFile;
    $noerror=true;
    if(!is_file($fromFile)) {
      $fromFile=$this->fsDir().$fromFile;
      $noerror=is_file($fromFile);
    }
    if($noerror) {
      if($newFileName) {
        //echo("<HR>da:".$fromFile."<BR>a:".$toPath.DIRECTORY_SEPARATOR.$newFileName.$ext);
	$op($fromFile,$toPath.DIRECTORY_SEPARATOR.$newFileName.$ext);
	$this->lastFile=substr($toPath.DIRECTORY_SEPARATOR.$newFileName.$ext,strlen($this->fsDir()));
      } else {
	//echo("<HR>da:".$fromFile."<BR>a:".$this->fsDir().$toPath.DIRECTORY_SEPARATOR.$justFile.$ext);
  	$op($fromFile,$this->fsDir().$toPath.DIRECTORY_SEPARATOR.$justFile.$ext);
	$this->lastFile=substr($this->fsDir().$toPath.DIRECTORY_SEPARATOR.$justFile.$ext,strlen($this->fsDir()));
      }
    } else {
      $this->lastFile=false;
    }
    return $this;
    
  }

  
  public function copyDirTo($toPath) { return $this->dirOperation("copy",$toPath);  }
  public function moveDirTo($toPath,$newDirName=false) {     return $this->dirOperation("rename",$toPath); }
  public function copyFileTo($toPath=false,$fromFile=false,$newFileName=false,$preserveExtension=false) { return $this->fileOperation("copy",$toPath,$fromFile,$newFileName,$preserveExtension); }
  public function moveFileTo($toPath,$fromFile=false,$newFileName=false,$preserveExtension=false) {  return $this->fileOperation("rename",$toPath,$fromFile,$newFileName,$preserveExtension); }

  
  public function removeFile($path=false) {
    if($path) {
      $path=$this->fsDir().$path;
      if(is_file($path))return unlink($path);
      return false;
    } else {
      if(is_array($this->files)) {
	foreach($this->files as $file) {
	  $toRemove=$this->path().DIRECTORY_SEPARATOR.$file;
	  if(is_file($toRemove))unlink($toRemove);
	}
      }
      return true;
    }
  }

  /*public function removeFile($file=false) {
    unlink($file);
    }*/

  public function removeDir($path=false) {
    if($path) {
      $this->removeRecursive($path);
    } else {
      $this->removeRecursive($this->path());
    }
  }
  
  private function removeRecursive($path) {
    if (is_dir($path)) {
      $objects = scandir($path);
      foreach ($objects as $object) {
	if ($object != "." && $object != "..") {
	  if (filetype($path."/".$object) == "dir") rrmdir($path."/".$object); else unlink($path."/".$object);
	}
      }
      reset($objects);
      rmdir($path);
    }
  } 

  public function listRecursive($path=false) {
  	if(!$path)$path=$this->path();
  	$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
    return $objects;
  }

}


?>