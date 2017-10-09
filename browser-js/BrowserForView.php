<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/30
 * Time: 12:53
 */

class BrowserForView
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

        $cwd = '/Users/yagihash/PhpstormProjects/senbon-xss/browser-js';
        $cmd = 'timeout -sINT 3s ' . $node . ' view.js';

        `echo $cmd >> /tmp/hoge.txt`;
        `echo $key >> /tmp/hoge.txt`;
        `echo $url >> /tmp/hoge.txt`;
        `echo $flag >> /tmp/hoge.txt`;


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