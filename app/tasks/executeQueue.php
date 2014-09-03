<?php
    namespace ThinTask;
    use Thin\Cli;
    use Thin\Cron;

    class executeQueue
    {
        public static function boot()
        {
            Cli::show("Start of execution", 'COMMENT');
            $cron = Cron::instance();
            $cron->flush();
            Cli::show("End of execution", 'COMMENT');
        }
    }
