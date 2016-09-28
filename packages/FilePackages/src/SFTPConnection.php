<?php
namespace FilePackages;

use App\Http\Requests;
use Exception;

/**
 * Class SFTPConnection
 * SFTP Class
 */
class SFTPConnection
{
    private $connection;
    private $sftp;

    public function __construct($host, $port = 22)
    {
        $this->connection = @ssh2_connect($host, $port);
        if (!$this->connection)
            throw new Exception("Could not connect to $host on port $port.");
    }

    public function login($username, $password)
    {
        if (!@ssh2_auth_password($this->connection, $username, $password))
            throw new Exception("Could not authenticate with username $username " . "and password $password.");
        $this->sftp = @ssh2_sftp($this->connection);
        if (!$this->sftp)
            throw new Exception("Could not initialize SFTP subsystem.");
    }

    public function uploadFile($local_file, $remote_file, $mode = 0777)
    {
        $sftp = $this->sftp;
        //ssh2_scp_send($sftp, $local_file, $remote_file, $mode);

        $stream = @fopen("ssh2.sftp://$sftp$remote_file", 'w');
        if (!$stream)
            throw new Exception("Could not open file: $remote_file");
        //$data_to_send = @file_get_contents($local_file);
        $data_to_send = $local_file;
        if ($data_to_send === false)
            throw new Exception("Could not open local file: $local_file.");
        if (@fwrite($stream, $data_to_send) === false)
            throw new Exception("Could not send data from file: $local_file.");
        @fclose($stream);
        return true;

    }

    function scanFilesystem($remote_file)
    {
        $sftp = $this->sftp;
        $dir = "ssh2.sftp://$sftp$remote_file";
        $tempArray = array();
        $handle = opendir($dir);
        // List all the files
        while (false !== ($file = readdir($handle))) {
            if (substr("$file", 0, 1) != ".") {
                if (is_dir($file)) {
//                $tempArray[$file] = $this->scanFilesystem("$dir/$file");
                } else {
                    $tempArray[] = $file;
                }
            }
        }
        closedir($handle);
        return $tempArray;
    }

    public function receiveFile($remote_file)
    {
        $sftp = $this->sftp;
        $stream = @fopen("ssh2.sftp://$sftp$remote_file", 'r');
        if (!$stream)
            throw new Exception("Could not open file: $remote_file");
        $size = $this->getFileSize($remote_file);
        $contents = '';
        $read = 0;
        $len = $size;
        while ($read < $len && ($buf = fread($stream, $len - $read))) {
            $read += strlen($buf);
            $contents .= $buf;
        }
        @fclose($stream);
        return $contents;
    }

    public function getFileSize($file)
    {
        $sftp = $this->sftp;
        return filesize("ssh2.sftp://$sftp$file");
    }

    public function checkFile($remote_file)
    {
        $sftp = $this->sftp;
        if (file_exists("ssh2.sftp://$sftp$remote_file")) {
            return true;
        } else {
            return false;
        }
    }

    public function checkDir($upload_path)
    {
        $sftp = $this->sftp;
        if (is_dir("ssh2.sftp://$sftp$upload_path")) {
            return true;
        } else {
            return false;
        }
    }

    public function makeDir($upload_path, $mode = 0777)
    {
        $sftp = $this->sftp;
        $result = mkdir("ssh2.sftp://$sftp$upload_path", $mode, true);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteFile($remote_file)
    {
        $sftp = $this->sftp;
        return ssh2_sftp_unlink($sftp, $remote_file);
    }

    public function deleteDirectory($remote_dir)
    {
        $sftp = $this->sftp;
        if (!file_exists("ssh2.sftp://$sftp$remote_dir")) return true;
        if (!is_dir("ssh2.sftp://$sftp$remote_dir") || is_link("ssh2.sftp://$sftp$remote_dir")) {
            return ssh2_sftp_unlink($sftp, $remote_dir);
        }
        foreach (scandir("ssh2.sftp://$sftp$remote_dir") as $item) {
            echo $item;
            if ($item == '.' || $item == '..') continue;
            if (!$this->deleteDirectory("ssh2.sftp://$sftp$remote_dir" . "/" . $item)) {
                chmod("ssh2.sftp://$sftp$remote_dir" . "/" . $item, 0777);
                if (!$this->deleteDirectory("ssh2.sftp://$sftp$remote_dir" . "/" . $item)) return false;
            };
        }

        return rmdir("ssh2.sftp://$sftp$remote_dir");
    }

}