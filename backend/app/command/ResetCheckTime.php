<?php

declare(strict_types=1);

namespace app\command;

use app\admin\model\Users;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class ResetCheckTime extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('resetchecktime')
            ->setDescription('the resetchecktime command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $output->writeln('resetchecktime start');

        Users::where('id', '>', 0)->update(['check_name_times' => sysconfig('check_name', 'check_name_times')]);
        $output->writeln('resetchecktime end');
    }
}
