<?php


namespace Overlu\Rpc;


use Overlu\Rpc\Util\Command;

class Server
{
    protected $pidFile;

    public function __construct($server = null)
    {
        $pidFilename = strtolower(str_replace('\\', '-', $server ?: get_class($this)));
        $this->pidFile = storage_path() . DIRECTORY_SEPARATOR . $pidFilename . '.pid';
        $this->checkPcntl();
    }

    /**
     * 创建守护进程核心函数
     */
    protected function demonize()
    {
        if (php_sapi_name() !== 'cli') {
            exit('should run in cli');
        }
        // 创建子进程
        $pid = pcntl_fork();
        if ($pid === -1) {
            exit('fork failed');
        }

        if ($pid > 0) {
            // 终止父进程
            exit($pid);
        }
        // 在子进程中创建新的会话
        if (posix_setsid() === -1) {
            exit('could not detach');
        }
        // 改变工作目录
        chdir('/');
        $pid = pcntl_fork();
        if ($pid === -1) {
            exit('fork failed');
        }

        if ($pid) {
            //  再一次退出父进程，子进程成为最终的守护进程
            exit(0);
        }
        // 重设文件创建的掩码
        umask(0);
        $fp = fopen($this->pidFile, 'w') or exit("can't create pid file");
        // 把当前进程的id写入到文件中
        fwrite($fp, posix_getpid());
        fclose($fp);
        // 关闭文件描述符
        @fclose(STDOUT);
        @fclose(STDERR);
        $STDOUT = fopen('/dev/null', "a");
        $STDERR = fopen('/dev/null', "a");
        // 运行守护进程的逻辑
        $this->job();
        return true;
    }

    /**
     * 守护进程的任务
     */
    public function job()
    {
    }

    /**
     * 获取守护进程的id
     * @return int
     */
    protected function getPid(): int
    {
        // 判断存放守护进程id的文件是否存在
        if (!file_exists($this->pidFile)) {
            return 0;
        }
        $pid = (int)file_get_contents($this->pidFile);
        unlink($this->pidFile);
        if (posix_kill($pid, SIG_DFL)) {
            return $pid;
        }
        return 0;
    }

    /**
     * 判断pcntl拓展
     */
    protected function checkPcntl(): void
    {
        !function_exists('pcntl_signal') && Command::error('php pcntl extension not exist!');
    }

    /**
     * @param bool $demonize 开启守护进程
     */
    public function start(bool $demonize = false): void
    {
        if ($pid = $this->getPid()) {
            Command::warning('already running on pid: ' . $pid);
        } else {
            $demonize ? $this->demonize() : $this->job();
        }
    }

    /**
     * 停止守护进程
     */
    public function stop(): void
    {
        if (Command::checkSupervisord()) {
            Command::warning('supervisor is running, stop supervisorctl first, and you can execute: sudo supervisorctl stop all');
        }
        $pid = $this->getPid();
        if ($pid > 0) {
            //通过向进程id发送终止信号来停止进程
            posix_kill($pid, SIGTERM);
            unlink($this->pidFile);
        }
    }

    /**
     * 查看进程状态
     */
    public function status()
    {
        if ($pid = $this->getPid()) {
            Command::info('is running on pid: ' . $pid);
        } else {
            Command::line('not running');
        }
    }

    /**
     * 重启进程
     * @param bool $demonize
     */
    public function reload(bool $demonize = false): void
    {
        $this->stop();
        $this->start($demonize);
    }
}
