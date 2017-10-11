<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/30
 * Time: 12:53
 */

class BrowserForClick
{
    function setUp()
    {
    }

    function perform()
    {
        $node = $this->args['node'];

        $key = $this->args['key'];
        $url = $this->args['url'];
        $flag = $this->args['flag'];

        $descriptorspec = [
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("file", "/tmp/error-output.txt", "a"),
        ];

        $cwd = $this->args['cwd'];
        $cmd = 'timeout -sINT 5s ' . $node . ' click.js';

        $env = [
            'KEY' => $key,
            'FLAG' => $flag,
            'URL' => $url,
        ];

        $process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);

        if (is_resource($process)) {
            proc_close($process);
        }
    }

    function tearDown()
    {
    }

}
