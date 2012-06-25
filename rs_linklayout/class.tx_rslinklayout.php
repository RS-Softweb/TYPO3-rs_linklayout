<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008-2009 Rene Staeker <typo3@rs-softweb.de>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Script 'class.tx_rslinklayout.php'
 *
 * @author	Rene Staeker <typo3@rs-softweb.de>
 */

class tx_rslinklayout {
    var $cObj;    // reference to the calling object.

    function main($content,$conf)    {

      global $TSFE;

        /***************************************************************
        *  BEGIN - Break if no link class is set from Typo3
        ***************************************************************/
        if (strpos($content["TAG"],'class=')===FALSE) {
          return $content["TAG"];
        }
        /***************************************************************
        *  END - Break if no link class is set from Typo3
        ***************************************************************/
        
        /***************************************************************
        *  BEGIN - Link target image creation 
        ***************************************************************/
        if ($conf["linkTargetEnabled"] == True){
          if ($content["TYPE"] == "url"){
            $linkImg = $this->cObj->IMAGE($conf["linkTargetExt."]);
            $content["TAG"] = $this->replace_link_params($content["TAG"],$conf["linkTargetExtParams"]);
          }
          elseif ($content["TYPE"] == "mailto"){
            $linkImg = $this->cObj->IMAGE($conf["linkTargetMailto."]);
            $content["TAG"] = $this->replace_link_params($content["TAG"],$conf["linkTargetMailtoParams"]);
          } 
          else {
            $linkImg = $this->cObj->IMAGE($conf["linkTargetInt."]);
            $content["TAG"] = $this->replace_link_params($content["TAG"],$conf["linkTargetIntParams"]);
          }
        }
        /***************************************************************
        *  END - Link target image creation 
        ***************************************************************/

        /***************************************************************
        *  BEGIN - Link filetype image creation 
        ***************************************************************/
        if ($conf["linkFiletypeEnabled"] == True){

          /***************************************************************
          *  BEGIN - Prepare fileicons array 
          ***************************************************************/
          $extensions = $conf["linkFiletypeList"];
          $directory = t3lib_extMgm::siteRelPath("rs_linklayout")."res/"; 

          $fileicons = array();
          $handle = opendir($directory); 
          while ($file = readdir ($handle)) {
            $file = $directory.$file;
            if (!is_file($file)) continue;

            if ( (strpos($file,'.gif')>0) && (!(strpos($extensions,basename($file,".gif"))===false)) ) {
              $fileicons[basename($file,".gif")] = $file;
            }
          } 
          /***************************************************************
          *  END - Prepare fileicons array 
          ***************************************************************/
        
          $linkFile = '';
          $url = $content['url'];
        
          if ($fileicons[substr($url,strrpos($url,'.')+1)] <> '') {
            $linkFileArray = $this->conf['ImageCObject.'];
            $linkFileArray['file'] = $fileicons[substr($url,strrpos($url,'.')+1)];
            $linkFileArray['file.']['maxH'] = $conf["linkFiletypeHeight"];
            $linkFileArray['wrap'] = $conf["linkFiletypeWrap"];
            $linkFileArray['stdWrap.']['addParams.']['alt'] = strtoupper(substr($url,strrpos($url,'.')+1));
            $linkFileArray['stdWrap.']['addParams.']['title'] = strtoupper(substr($url,strrpos($url,'.')+1));
            $linkFile = $this->cObj->IMAGE($linkFileArray);
          }
        }
        /***************************************************************
        *  END - Link filetype image creation 
        ***************************************************************/

        return $content["TAG"].$linkImg.$linkFile;
    }


    /***************************************************************
    *  BEGIN - Extend the link params (not used yet)
    ***************************************************************/
/*
    function extend_link_params($original,$extension,$delimiter) {
      $originals_temp = array();
      $originals = array();
      $extensions_temp = array();
      $extensions = array();
      $extended_temp = array();
      $extended = array();

      $original = substr($original,strpos($original,'<a ')+3,-1);
      $original = trim($original,' "');
      $originals_temp = explode('" ', $original);
      for ($i=0;$i<count($originals_temp);$i++) {
        $originals[substr($originals_temp[$i],0,strpos($originals_temp[$i],'="'))] = substr($originals_temp[$i],strpos($originals_temp[$i],'="')+2);
      }

      $extension = trim($extension);
      $extensions_temp = explode(" ", $extension);
      for ($i=0;$i<count($extensions_temp);$i++) {
        $extensions[substr($extensions_temp[$i],0,strpos($extensions_temp[$i],'='))."_ex"] = substr($extensions_temp[$i],strpos($extensions_temp[$i],'=')+1);
      }
      
      $extended_temp = array_merge($originals,$extensions);
      ksort($extended_temp);
      for ($i=0;$i<count($extended_temp);$i++) {
        $key = key($extended_temp);
        if ($extended_temp[$key.'_ex']<>'') {
          $extended[$key] = $extended_temp[$key].$delimiter.$extended_temp[$key.'_ex'];
          next($extended_temp);
          $i++;
        } else {
          $extended[$key] = $extended_temp[$key];
        }
        next($extended_temp);   
      }

      return $this->recreate_link($extended);
    }
*/
    /***************************************************************
    *  END - Extend the link params (not used yet)
    ***************************************************************/

    /***************************************************************
    *  BEGIN - Replace the link params
    ***************************************************************/
    function replace_link_params($original,$extension) {
      $originals_temp = array();
      $originals = array();
      $extensions_temp = array();
      $extensions = array();
      $extended_temp = array();
      $extended = array();

      $original = substr($original,strpos($original,'<a ')+3,-1);
      $original = trim($original,' "');
      $originals_temp = explode('" ', $original);
      for ($i=0;$i<count($originals_temp);$i++) {
        $originals[substr($originals_temp[$i],0,strpos($originals_temp[$i],'="'))] = substr($originals_temp[$i],strpos($originals_temp[$i],'="')+2);
      }

      // remove useless whitespaces (thanks "Daniel K.")
      $extension = trim($extension);
      $extension = str_replace('  ',' ',$extension);
      $extension = str_replace('  ',' ',$extension);

      $extensions_temp = explode(" ", $extension);

      // clean the array values (remove " or ') (thanks "Daniel K.")
			for ($i=0;$i<count($extensions_temp);$i++) {
        $extensions_temp[$i] = str_replace('"','',$extensions_temp[$i]);
        $extensions_temp[$i] = str_replace('\'','',$extensions_temp[$i]);
      } //end

      for ($i=0;$i<count($extensions_temp);$i++) {
        $extensions[substr($extensions_temp[$i],0,strpos($extensions_temp[$i],'='))."_ex"] = substr($extensions_temp[$i],strpos($extensions_temp[$i],'=')+1);
      }
      
      $extended_temp = array_merge($originals,$extensions);
      ksort($extended_temp);
      for ($i=0;$i<count($extended_temp);$i++) {
        $key = key($extended_temp);
        if ($extended_temp[$key.'_ex']<>'') {
          $extended[$key] = $extended_temp[$key.'_ex'];
          next($extended_temp);
          $i++;
        } else {
          // this is the new case (thanks "Daniel K.")
          // if key is from extension list and has no occurrance on original list
          // put it to extended array without suffix "_ex"
          if (strpos($key, "_ex") > 0 ) {
            $extended[substr($key,0,strpos($key, "_ex"))] = $extended_temp[$key];
          } 
          // key from original list that has no occurrance on extension list
          // put it to extended array as-is
          else {
            $extended[$key] = $extended_temp[$key];
          }
        }
        next($extended_temp);   
      }

      return $this->recreate_link($extended);
    }
    /***************************************************************
    *  END - Replace the link params
    ***************************************************************/

    /***************************************************************
    *  BEGIN - Recreate the link
    ***************************************************************/
    function recreate_link($params) {
      $link = '<a ';
      for ($i=0;$i<count($params);$i++) {
        $link .= key($params).'="'.$params[key($params)].'" ';
        next($params);
      }
      $link .= '>';
      
      return $link;
    }
    /***************************************************************
    *  END - Recreate the link
    ***************************************************************/

}
?>
