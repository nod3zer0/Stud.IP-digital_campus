<?php

namespace Studip\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupAdmissionRules extends Command
{
    protected static $defaultName = 'cleanup:admission-rules';

    protected function configure(): void
    {
        $this->setDescription('Cleanup admission-rules.');
        $this->setHelp('Deletes entries in %admissions tables.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        require_once 'lib/classes/admission/CourseSet.class.php';

        $course_set = new \CourseSet();

        $sql = "SELECT * FROM
(
SELECT rule_id,'ConditionalAdmission' as class FROM `conditionaladmissions`
UNION
SELECT rule_id,'CourseMemberAdmission' as class FROM `coursememberadmissions`
UNION
SELECT rule_id,'LimitedAdmission' as class FROM limitedadmissions
UNION
SELECT rule_id,'LockedAdmission' as class FROM lockedadmissions
UNION
SELECT rule_id,'ParticipantRestrictedAdmission' as class FROM participantrestrictedadmissions
UNION
SELECT rule_id,'PasswordAdmission' as class FROM passwordadmissions
UNION
SELECT rule_id,'TimedAdmission' as class FROM timedadmissions
) a
LEFT JOIN courseset_rule USING(rule_id) WHERE set_id IS NULL";

        $c1 = $c2 = 0;
        \DBManager::get()->fetchAll($sql, null, function ($data) use (&$c1, &$c2, $output) {
            $c1++;
            $class_name = '\\' . $data['class'];
            if (class_exists($class_name)) {
                $rule = new $class_name($data['rule_id']);
                if ($rule->getId() === $data['rule_id']) {
                    $output->writeln(sprintf('deleting: %s with id: %s', $rule->getName(), $rule->getId()));
                    $c2++;
                    $rule->delete();
                }
            }
        });
        $output->writeln(sprintf('found: %s deleted: %s', $c1, $c2));

        return Command::SUCCESS;
    }
}
