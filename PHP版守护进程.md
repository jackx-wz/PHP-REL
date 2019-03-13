# 概念
## 进程组：
	• 每个进程都属于一个进程组
	• 每个进程组都有进程组id，该id是该进程组组长的pid
	• 一个进程只能为它自己及它的子进程设置进程组id

## 会话：
	• 是一个或多个进程组的集合
	• 只有会话组的领导进程能够打开控制终端，打开的控制终端实际上是伪终端
	• 进程组的领导进程不能创建会话
	• 进程创建会话后，会与以前的控制终端、会话和进程组断开联系，该进程会成为新的进程组的领导进程
	• 创建的新会话没有控制终端
	• 创建新会话的时候，是进程执行fork，然后主进程退出，子进程设置setsid
	• setsid这个函数只能是非组长进程调用
	• 会话组长终止时会向会话中前台进程组和挂起进程发送终止信号 SIGHUP

## 控制终端：
会话中的控制进程中的文件描述符0、1、2一般都指向控制终端，这样可以通过控制终端进行输入输出

## 进程挂起：
将进程暂停并转入后台

## 文件描述符：
fork出的子进程会基础父进程的所有文件描述符
文件描述符是文件在内核中的索引，通过它可以操作文件

## 守护进程创建过程：
1. 转入后台：fork进程，父进程终止，子进程转入后台继续执行
2. 切断与当前控制终端、会话、用户组的联系：子进程调用setsid，子进程会成为新的会话领导进程，进程组组长，并且没有控制终端
3. 忽略SIGHUP信号：会话领导进程终止时会向会话中所有进程发送SIGHUP信号
4. 让进程不能打开任何控制终端：子进程fork，子进程退出，子子进程调用setsid。子子进程不是领导进程，所以不能打开控制终端。
5. 让进程与启动设备无关：将工作目录修改为 “/”。一般这样就与具体设备无关了，具体设备可以卸载
6. 关闭继承的文件描述符：打开一个空设备，将STDIN、STDOUT、STDERR等指向它
7. 重新设置umask掩码，一般设置为0，可以任何操作文件


## 具体代码

```
<?php

/**
 * Created by PhpStorm.
 * User: xdc
 * Date: 2019/3/13
 * Time: 4:01 PM
 */
class DaemonProcess
{
    private $output = '/dev/null';

    public function __construct(){
        set_time_limit(0);
        $this->init();
        $this->work();
    }

    public function init(){
        $this->nohup();
        $this->createSession();
        $this->ignoreSIGHUP();
        $this->forbidTerminal();
        $this->ignoreDevice();
        $this->closeFD();
        $this->resetUmask();
    }

    private function nohup(){
        $pid = pcntl_fork();

        if($pid != 0){
            exit(0);
        }
    }

    private function createSession(){
        posix_setsid();
    }

    private function ignoreSIGHUP(){
        pcntl_signal(SIGHUP, SIG_IGN, false);
    }

    private function forbidTerminal(){
        $pid = pcntl_fork();

        if($pid != 0){
            exit(0);
        }

        posix_setsid();
    }

    private function ignoreDevice(){
        chdir('/');
    }

    private function closeFD(){
        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);

        /**
         * 关闭了标准输入、输出、错误之后，最先打开的3个fd将自动变成新的标准输入、输出、错误
         */
        $stdin = fopen($this->output, 'r');
        $stdout = fopen($this->output, 'a');
        $stderr = fopen($this->output, 'a');
    }

    private function resetUmask(){
        umask(0);
    }

    //这里执行具体业务
    public function work(){
        $i = 0;

        while($i < 100){
            file_put_contents('/tmp/work_index', $i."\n", FILE_APPEND);
            $i++;
            sleep(1);
        }
    }
}

$daemon = new DaemonProcess();
die();   //测试用，不退出的话会一直作为守护进程执行
```