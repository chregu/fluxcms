<?php

/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/Task.php';

/**
 * Executes a command on the shell.
 *
 * @author   Andreas Aderhold <andi@binarycloud.com>
 * @author   Hans Lellelid <hans@xmpl.org>
 * @version  $Revision: 1.12 $
 * @package  phing.tasks.system
 */
class ExecTask extends Task {

    /**
     * Command to execute.
     * @var string
     */
    protected $command;
    
    /**
     * Working directory.
     * @var File
     */
    protected $dir;
    
    /**
     * Operating system.
     * @var string
     */
    protected $os;
    
    /**
     * Whether to escape shell command using escapeshellcmd().
     * @var boolean
     */
    protected $escape = false;
    
    /**
     * Where to direct output.
     * @var File
     */
    protected $output;
    
    /**
     * Where to direct error output.
     * @var File
     */
    protected $error;
    
    /**
     * Main method: wraps execute() command.
     * @return void
     */
    public function main() {
        $this->execute();
    }
    
    /**
     * Executes a program and returns the return code.
     * Output from command is logged at INFO level.
     * @return int Return code from execution.
     */
    public function execute() {
    
         // test if os match
        $myos = Phing::getProperty("os.name");
        $this->log("Myos = " . $myos, PROJECT_MSG_VERBOSE);
        if (($this->os !== null) && (strpos($os, $myos) === false)) {
            // this command will be executed only on the specified OS
            $this->log("Not found in " . $os, PROJECT_MSG_VERBOSE);
            return 0;
        }
        
         if ($this->dir !== null) {
            if ($this->dir->isDirectory()) {
                $currdir = getcwd();
                @chdir($this->dir->getPath());
            } else {
                throw new BuildException("Can't chdir to:" . $this->dir->__toString());
            }
        }


        if ($this->escape == true) {
            // FIXME - figure out whether this is correct behavior
            $this->command = escapeshellcmd($this->command);
        }

        $this->log("Executing command: " . $this->command, PROJECT_MSG_VERBOSE);
        
        if ($this->error !== null) {
            $this->command .= ' 2> ' . $this->error->getPath();
            $this->log("Writing error output to: " . $this->error->getPath());
        }
        
        if ($this->output !== null) {
            $this->command .= ' 1> ' . $this->output->getPath();
            $this->log("Writing standard output to: " . $this->output->getPath());
        }
        
        // If neither output nor error are being written to file
        // then we'll redirect error to stdout so that we can dump
        // it to screen below.

        if ($this->output === null && $this->error === null) {
            $this->command .= ' 2>&1';            
        }
                
        $output = array();
        $return = null;
        exec($this->command, $output, $return);

        if ($this->dir !== null) {
            @chdir($currdir);
        }

        foreach($output as $line) {
            $this->log($line,  PROJECT_MSG_VERBOSE);
        }
        
        return $return;
    }

    /**
     * The command to use.
     * @param mixed $command String or string-compatible (e.g. w/ __toString()).
     */
    function setCommand($command) {
        $this->command = "" . $command;
    }
    
    /**
     * Whether to use escapeshellcmd() to escape command.
     * @param boolean $escape
     */
    function setEscape($escape) {
        $this->escape = (bool) $escape;
    }
    
    /**
     * Specify the workign directory for executing this command.
     * @param File $dir
     */
    function setDir(File $dir) {
        $this->dir = $dir;
    }
    
    /**
     * Specify OS (or muliple OS) that must match in order to execute this command.
     * @param string $os
     */
    function setOs($os) {
        $this->os = (string) $os;
    }
    
    /**
     * File to which output should be written.
     * @param File $output
     */
    function setOutput(File $f) {
        $this->output = $f;
    }
    
    /**
     * File to which error output should be written.
     * @param File $output
     */
    function setError(File $f) {
        $this->error = $f;
    }
    
}

