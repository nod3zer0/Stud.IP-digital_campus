<?php


class RevampQuestionnaires extends Migration
{
    public function description()
    {
        return 'Better questionnaires and no old evaluations for Stud.IP';
    }


    public function up()
    {
        $query = 'INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)';
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            'name'        => 'EVAL_ENABLE',
            'description' => 'Sollen die alten Evaluationen weiterhin eingeschaltet bleiben? Achtung, die alten Evaluationen werden in einem zukünftigen Stud.IP-Release entfernt.',
            'range'       => 'global',
            'type'        => 'boolean',
            'value'       => '0',
            'section'     => 'evaluation'
        ]);

        //Umbau der Fragebögen, sodass sie nicht mehr die etask-Tabellen verwenden:
        DBManager::get()->exec("
            ALTER TABLE `questionnaire_questions`
            ADD COLUMN `questiontype` varchar(64) NOT NULL DEFAULT '' AFTER `questionnaire_id`,
            ADD COLUMN `internal_name` varchar(128) DEFAULT NULL AFTER `questiontype`,
            ADD COLUMN `questiondata` text NOT NULL DEFAULT '' AFTER `internal_name`
        ");

        $allquestions = DBManager::get()->prepare("
            SELECT * FROM `questionnaire_questions`
        ");
        $allquestions->execute();
        $updatequestion = DBManager::get()->prepare("
            UPDATE `questionnaire_questions`
            SET `questiondata` = :questiondata,
                `questiontype` = :questiontype
            WHERE `question_id` = :question_id
        ");
        $get_etask = DBManager::get()->prepare("
            SELECT * FROM `etask_tasks` WHERE `id` = ?
        ");

        while ($question = $allquestions->fetch(PDO::FETCH_ASSOC)) {
            $get_etask->execute([$question['etask_task_id']]);
            $etask = $get_etask->fetch(PDO::FETCH_ASSOC);

            $task = json_decode($etask['task'], true);
            $options = array_map(function ($answer) { return $answer['text']; }, (array) $task['answers']);
            $scores = array_map(function ($answer) { return $answer['score']; }, (array) $task['answers']);

            if ($etask['type'] === 'multiple-choice') {
                //Vote or Test
                $questiontype = array_sum($scores) > 0 ? 'Test' : 'Vote';
                $questiondata = [
                    'description' => $etask['description'],
                    'multiplechoice' => $task['type'] === 'multiple' ? '1' : '0',
                    'options' => $options
                ];
            } else {
                //Most of the times Freetext
                $questiontype = ucfirst($etask['type']);
                $questiondata = $task;
                $questiondata['description'] = $etask['description'];
            }
            $questiondata = array_merge($questiondata, json_decode($etask['options'], true));

            $updatequestion->execute([
                'question_id' => $question['question_id'],
                'questiondata' => json_encode($questiondata),
                'questiontype' => $questiontype
            ]);
        }

        DBManager::get()->exec("
            DELETE FROM `etask_tasks`
            WHERE `id` IN (SELECT `etask_task_id` FROM `questionnaire_questions`)
        ");

        DBManager::get()->exec("
            ALTER TABLE `questionnaire_questions`
            DROP COLUMN `etask_task_id`
        ");

        DBManager::get()->exec("
            ALTER TABLE `questionnaires`
            CHANGE COLUMN `resultvisibility` `resultvisibility` enum('always','never','afterending', 'afterparticipation') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'always'
        ");

        //Tests aus den Fragebögen löschen:
        DBManager::get()->exec("
            DELETE FROM `questionnaire_questions`
            WHERE `questiontype` = 'Test'
        ");
        //Dann noch die jetzt vielleicht leeren Fragebögen abräumen:
        DBManager::get()->exec("
            DELETE FROM `questionnaires`
            WHERE `questionnaire_id` NOT IN (SELECT `questionnaire_id` FROM `questionnaire_questions`)
        ");
        DBManager::get()->exec("
            DELETE FROM `questionnaire_anonymous_answers`
            WHERE `questionnaire_id` NOT IN (SELECT `questionnaire_id` FROM `questionnaires`)
        ");
        DBManager::get()->exec("
            DELETE FROM `questionnaire_assignments`
            WHERE `questionnaire_id` NOT IN (SELECT `questionnaire_id` FROM `questionnaires`)
        ");
        DBManager::get()->exec("
            DELETE FROM `questionnaire_answers`
            WHERE `question_id` NOT IN (SELECT `question_id` FROM questionnaire_questions)
        ");

    }


    public function down()
    {

    }
}
