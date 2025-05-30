<?php
    require 'info.php';
    
    function sitemap_build($action, $settings, $board) {
        global $config, $pdo;
        
        // Possible values for $action:
        //	- all (rebuild everything, initialization)
        //	- news (news has been updated)
        //	- boards (board list changed)
        //	- post (a post has been made)
        //	- thread (a thread has been made)

        if ($action != 'all') { 	
            if ($action != 'post-thread' && $action != 'post-delete')
                return;
        
            if (isset($settings['regen_time']) && $settings['regen_time'] > 0) {
                if ($last_gen = @filemtime($settings['path'])) {
                    if (time() - $last_gen < (int)$settings['regen_time'])
                        return; // Too soon
                }
            }
        }

        $action = generation_strategy('sb_sitemap', array());

        if ($action == 'delete') {
            file_unlink($settings['path']);
        }
        elseif ($action == 'rebuild') {
            $boards = explode(' ', $settings['boards']);
        
            $threads = array();
        
            foreach ($boards as $board) {
                // Unified posts table: get all threads for this board
                $query = prepare("SELECT `id`, `id` AS `thread_id`, `slug`, 
                    (SELECT `time` FROM ``posts`` WHERE `board` = :board AND (`thread` = `thread_id` OR `id` = `thread_id`) ORDER BY `time` DESC LIMIT 1) AS `lastmod`
                    FROM ``posts`` WHERE `board` = :board AND `thread` IS NULL");
                $query->bindValue(':board', $board);
                $query->execute() or error(db_error($query));
                $threads[$board] = $query->fetchAll(PDO::FETCH_ASSOC);
            }
                
            file_write($settings['path'], Element('themes/sitemap/sitemap.xml', Array(
                'settings' => $settings,
                'config' => $config,
                'threads' => $threads,
                'boards' => $boards,
            )));
        }
    }