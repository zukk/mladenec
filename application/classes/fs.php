<?php
/**
 * Класс для работы с файловой системой
 *
 * @author pks
 */
class Fs {
    /**
     * Get directory listing
     * 
     * @param string $dir absolute path to a directory
     * @param boolean $recursive
     * @param string $exceptions - comma or space separated exceptions list
     * @return boolean
     */
    public static function ls($dir, $recursive = FALSE, $exceptions = '') {
        
        /* prevent listing directories out of a dir */
        $dir = str_replace(array('./','../'), '', $dir);
        
        if ( ! file_exists($dir)) return FALSE;
        
        $exceptions_arr = array();
        
        if ( ! empty($exceptions)) {
            
            if (is_array($exceptions)) {
                
                $exceptions_arr = $exceptions;
                
            } else {
                
                $exceptions = str_replace(',', ' ', $exceptions);
                $exceptions = preg_replace('/\s+/u', ' ', $exceptions);
                $exceptions_arr = explode(' ', $exceptions);
            }
        }
        
        $cache_key = 'fs_ls_' . md5($dir . '_' . ($recursive?'1':'0') . '_' . implode(',', $exceptions_arr));
        $listing = Cache::instance()->get($cache_key);
        
        if ( ! empty($listing) AND is_array($listing)) return $listing;
        
        $listing = array();
        
        if ($handle = opendir($dir)) {
            
            while (false !== ($entry = readdir($handle))) {
                
                if ($entry == "." OR $entry == "..") continue;
                if ($entry[0] === '.' OR $entry[strlen($entry)-1] === '~') continue;
                if (FALSE !== array_search($entry,$exceptions_arr)) continue;
                
                if (is_dir($dir.'/'.$entry)) {
                    
                    $sub_listing = self::ls($dir.'/'.$entry, $recursive, $exceptions);
                    if (FALSE === $sub_listing) continue;
                    $listing = array_merge($listing, $sub_listing);
                    
                } else {
                    
                    $listing[] = $dir.'/'.$entry;
                    
                }
            }
            
            closedir($handle);
            
        } else return FALSE;
        
        Cache::instance()->set($cache_key, $listing);
        
        return $listing;
    }
    
    /**
     * recursively deleting directory
     * 
     * @param string $dir
     */
    public static function rrmdir($dir) { 
        if (is_dir($dir)) { 
            $objects = scandir($dir); 
            foreach ($objects as $object) { 
                if ($object != "." && $object != "..") { 
                    if (filetype($dir."/".$object) == "dir") self::rrmdir($dir."/".$object); else unlink($dir."/".$object); 
                } 
            } 
                reset($objects); 
                rmdir($dir); 
            } 
        } 
    }

?>