<?php

declare(strict_types=1);

namespace app\command;

use app\common\lib\BaiDuApi;
use app\common\lib\wxApi;
use app\common\service\ThirdPartyService;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Test extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('test')
            ->setDescription('the test command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $output->writeln('test start');
    
        $api = new wxApi('mp');
        cache('wx_mp_access_token','55_fbmfSBcV0LDzAZiR7Y6dfV8-vAZVFWJvot8Z5W0rJIT8brmJU0MP8tYbOPKcDpcdVamx-PpJsYoQyE4fvjiSaYddTVhA4pDgzSuF2TSTlbR3L9_S8DgSCFwiICrqFVX12XxwG5GRGEymAlYfUALdAFADJU'); 

        echo "\n";
        $output->writeln('test end');
    }
}
