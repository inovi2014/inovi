<?php
    namespace ThinTask;
    use Thin\View;
    use Thin\Utils;
    use Thin\Cli;
    use Thin\Database;

    class cleanCache
    {
        public static function boot()
        {
            Cli::show("Start of execution", 'COMMENT');
            View::cleanCache();
            Database::cleanCache();
            Utils::cleanCache(true);
            Cli::show("End of execution", 'COMMENT');
        }
    }
