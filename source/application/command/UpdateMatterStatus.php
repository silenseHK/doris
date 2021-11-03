<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class UpdateMatterStatus extends Command
{

    protected function configure()
    {
        $this->setName('update_matter_status')->setDescription('更新问题状态');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("TestCommand:");
    }

}