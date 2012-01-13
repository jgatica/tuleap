<?php
/**
 * Copyright (c) Enalean, 2012. All Rights Reserved.
 * 
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * Manage generation of Apache Subversion configuration for project Authentication
 * and authorization
 * It generates the content of /etc/httpd/conf.d/codendi_svnroot.conf file
 */
abstract class SVN_Apache {
    private $projects = array();
    
    /**
     * Takes an array of DB row containing project query result.
     * Each row is a project
     * 
     * @param Array $projects 
     */
    public function __construct($projects) {
        $this->projects = $projects;
    }
    
    public function getFullConf() {
        $conf = $this->getHeaders();
        foreach ($this->projects as $row) {
            $conf .= $this->getOneProject($row);
        }
        return $conf;
    }
    
    /**
     *  Define specific log file for SVN queries
     * @return string 
     */
    protected function getHeaders() {
        $ret = "# Codendi SVN repositories\n\n";
        $ret = "# Custom log file for SVN queries\n";
        $ret = 'CustomLog logs/svn_log "%h %l %u %t %U %>s \"%{SVN-ACTION}e\"" env=SVN-ACTION'."\n\n";
        return $ret;
    }
        
    protected function getOneProject($row) {
        $conf = '';
        $conf .= "<Location /svnroot/".$row['unix_group_name'].">\n";
        $conf .= "    DAV svn\n";
        $conf .= "    SVNPath ".$GLOBALS['svn_prefix']."/".$row['unix_group_name']."\n";
        $conf .= "    SVNIndexXSLT \"/svn/repos-web/view/repos.xsl\"\n";
        $conf .= $this->getProjectAuthorization($row);
        $conf .= $this->getProjectAuthentication($row);
        $conf .= "</Location>\n\n";
        return $conf;
    }
    
    abstract protected function getProjectAuthentication($row);
    
    protected function getCommonAuthentication($projectName) {
        $conf = '';
        $conf .= "    Require valid-user\n";
        $conf .= "    AuthType Basic\n";
        $conf .= "    AuthName \"Subversion Authorization (".$this->escapeStringForApacheConf($projectName).")\"\n";
        return $conf;
    }
    
    protected function getProjectAuthorization($row) {
        $conf = "    AuthzSVNAccessFile ".$GLOBALS['svn_prefix']."/".$row['unix_group_name']."/.SVNAccessFile\n";
        return $conf;
    }
    
    /**
     * Replace double quotes by single quotes in project name (conflict with Apache realm name)
     * 
     * @param String $str
     * @return String
     */
    protected function escapeStringForApacheConf($str) {
        return strtr($str, "\"", "'");
    }
}

?>
