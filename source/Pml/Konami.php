<?php
/*
 * pop'n My List updater
 *
 * Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 *
 * LICENSE
 *
 * This source file is subject to the 2-cause BSD License(Simplified
 * BSD License) that is bundled with this package in the file LICENSE.
 * The license is also available at this URL:
 * https://github.com/fetus-hina/popnmylist/blob/master/LICENSE
 */
class Pml_Konami {
    static public function update(Pml_Config $config) {
        printf("%s(): 更新を開始\n", __METHOD__);
        $db = Pml_Db::get(); // db init
        $client = new Pml_Konami_Client($config);

        $db->beginTransaction();
        $db_songs = new Zend_Db_Table('songs');
        foreach($db_songs->fetchAll() as $row_song) {
            self::updateScore($client, $row_song->id, $row_song->genre);
            sleep(1);
        }

        printf("%s(): コミット開始\n", __METHOD__);
        $db->commit();
    }

    static private function updateScore($client, $song_id, $genre) {
        printf("%s(): [%s] %s\n", __METHOD__, $song_id, $genre);
        $music_page = $client->getMusicPage($song_id);
        self::updateScoreMedals($song_id, $music_page);
        self::updateScoreScores($song_id, $music_page);
    }

    static private function updateScoreMedals($song_id, Pml_Konami_Client_Page_Music $page) {
        $diff_map = array('N' =>  2, 'H' => 3, 'EX' => 4, '5' => 1);
        $medal_map =
            array(
                Pml_Konami_Client_Page_Music::MEDAL_NO_PLAY     => 1,
                Pml_Konami_Client_Page_Music::MEDAL_NO_CLEAR    => 2,
                Pml_Konami_Client_Page_Music::MEDAL_CLEAR       => 3,
                Pml_Konami_Client_Page_Music::MEDAL_NO_BAD      => 4,
                Pml_Konami_Client_Page_Music::MEDAL_PERFECT     => 5);
        $db_medals = new Zend_Db_Table('score_medals');
        foreach($diff_map as $diff_key => $db_diff_id) {
            $rows  = $db_medals->find($song_id, $db_diff_id);
            $medal = $page->getMedal($diff_key);
            if(count($rows) === 0) {
                if($medal === null) {
                    // 当該難易度なし かつ DB 行なし: OK
                } else {
                    // 当該難易度あり かつ DB 行なし: INSERT
                    $db_medals->insert(
                        array(
                            'song_id'       => $song_id,
                            'difficulty_id' => $db_diff_id,
                            'medal_id'      => $medal_map[$medal]));
                }
            } elseif(count($rows) === 1) {
                if($medal === null) {
                    // 当該難易度なし かつ DB 行あり: おかしい
                    $db_medals->delete(
                        array(
                            $db_medals->getAdapter()->quoteInto('song_id = ?', $song_id),
                            $db_medals->getAdapter()->quoteInto('difficulty_id = ?', $db_diff_id)));
                } else {
                    // 当該難易度あり かつ DB 行あり: 更新かそのまま
                    if((int)$rows[0]->medal_id !== $medal_map[$medal]) {
                        // メダルが違う: 更新
                        $db_medals->update(
                            array('medal_id' => $medal_map[$medal]),
                            array(
                                $db_medals->getAdapter()->quoteInto('song_id = ?', $song_id),
                                $db_medals->getAdapter()->quoteInto('difficulty_id = ?', $db_diff_id)));
                    }
                }
            } else {
                throw Pml_Exception('Too many rows matched');
            }
        }
    }

    static private function updateScoreScores($song_id, Pml_Konami_Client_Page_Music $page) {
        $diff_map = array('N' =>  2, 'H' => 3, 'EX' => 4, '5' => 1);
        $type_map = array('great' => 1, 'cool' => 2);
        $db_scores = new Zend_Db_Table('score_scores');
        foreach($diff_map as $diff_key => $db_diff_id) {
            foreach($type_map as $type_key => $db_type_id) {
                $rows  = $db_scores->find($song_id, $db_diff_id, $db_type_id);
                $score = $page->getScore($diff_key, $type_key);
                if($score === null) {
                    $db_scores->delete(
                        array(
                            $db_scores->getAdapter()->quoteInto('song_id = ?', $song_id),
                            $db_scores->getAdapter()->quoteInto('difficulty_id = ?', $db_diff_id),
                            $db_scores->getAdapter()->quoteInto('type_id = ?', $db_type_id)));
                } elseif(count($rows) === 0) {
                    $db_scores->insert(
                        array(
                            'song_id'       => $song_id,
                            'difficulty_id' => $db_diff_id,
                            'type_id'       => $db_type_id,
                            'score'         => $score));
                } else {
                    $db_scores->update(
                        array('score' => $score),
                        array(
                            $db_scores->getAdapter()->quoteInto('song_id = ?', $song_id),
                            $db_scores->getAdapter()->quoteInto('difficulty_id = ?', $db_diff_id),
                            $db_scores->getAdapter()->quoteInto('type_id = ?', $db_type_id)));
                }
            }
        }
    }
}
