<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\award\AwardEnrolmentStatuses;
use go1\util\award\AwardStatuses;
use go1\util\DateTime;

trait AwardMockTrait
{
    protected function createAward(Connection $db, array $options = [])
    {
        $data = $options['data'] ?? [];
        $data = !$data ? json_encode(null) : (is_scalar($data) ? $data : json_encode($data));

        $db->insert('award_award', [
            'revision_id' => $revisionId = isset($options['revision_id']) ? $options['revision_id'] : null,
            'instance_id' => isset($options['instance_id']) ? $options['instance_id'] : 0,
            'user_id'     => isset($options['user_id']) ? $options['user_id'] : 0,
            'title'       => isset($options['title']) ? $options['title'] : 'Example award',
            'description' => isset($options['description']) ? $options['description'] : '…',
            'tags'        => isset($options['tags']) ? $options['tags'] : '',
            'locale'      => isset($options['locale']) ? $options['locale'] : '',
            'data'        => $data,
            'published'   => isset($options['published']) ? $options['published'] : 1,
            'marketplace' => isset($options['marketplace']) ? $options['marketplace'] : 0,
            'quantity'    => isset($options['quantity']) ? round($options['quantity'], 2) : null,
            'expire'      => isset($options['expire']) ? $options['expire'] : null,
            'created'     => isset($options['created']) ? $options['created'] : time(),
        ]);
        $awardId    = $db->lastInsertId('award_award');
        $revisionId = $this->createAwardRevision($db, $awardId, $revisionId);

        $db->update('award_award', ['revision_id' => $revisionId], ['id' => $awardId]);

        if (isset($options['items']) && is_array($options['items'])) {
            foreach ($options['items'] as $item) {
                $weight = $item['weight'] ?? 0;
                $this->createAwardItem($db, $revisionId, $item['entity_id'], $item['quantity'], $weight);
            }
        }

        return $awardId;
    }

    protected function createAwardRevision(Connection $db, int $awardId, int $id = null)
    {
        $db->insert('award_revision', array_filter(['id' => $id, 'award_id' => $awardId, 'updated' => time()]));

        return $db->lastInsertId('award_revision');
    }

    protected function createAwardItem(Connection $db, int $awardRevId, int $entityId, float $quantity = null, $weight = null)
    {
        $db->insert('award_item', [
            'award_revision_id' => $awardRevId,
            'entity_id'         => $entityId,
            'quantity'          => $quantity ? round($quantity, 2) : $quantity,
            'weight'            => $weight ?? 0,
        ]);

        return $db->lastInsertId('award_item');
    }

    protected function createAwardAchievement(Connection $db, int $userId, int $awardItemId, int $created = null)
    {
        $db->insert('award_achievement', [
            'user_id'       => $userId,
            'award_item_id' => $awardItemId,
            'created'       => $created ?? time(),
        ]);

        return $db->lastInsertId('award_achievement');
    }

    protected function createAwardItemManual(Connection $db, array $options)
    {
        $options['data'] = isset($options['data'])
            ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data'])
            : [];
        $options['data'] = json_encode($options['data']);

        $db->insert('award_item_manual', [
            'award_id'        => $options['award_id'],
            'title'           => $options['title'] ?? null,
            'type'            => $options['type'] ?? null,
            'description'     => $options['description'] ?? null,
            'categories'      => $options['categories'] ?? null,
            'user_id'         => $options['user_id'] ?? 0,
            'entity_id'       => $options['entity_id'] ?? null,
            'verified'        => $options['verified'] ?? false,
            'verifier_id'     => $options['verifier_id'] ?? 0,
            'quantity'        => isset($options['quantity']) ? round($options['quantity'], 2) : null,
            'completion_date' => $options['completion_date'] ?? time(),
            'data'            => $options['data'],
            'published'       => $options['published'] ?? AwardStatuses::PUBLISHED,
            'weight'          => $options['weight'] ?? 0,
        ]);

        return $db->lastInsertId('award_item_manual');
    }

    protected function createAwardEnrolment(Connection $db, array $options)
    {
        $data = isset($options['data'])
            ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data'])
            : [];
        $data = json_encode($data);

        $db->insert('award_enrolment', [
            'award_id'    => $options['award_id'],
            'user_id'     => $options['user_id'],
            'instance_id' => $options['instance_id'],
            'expire'      => isset($options['expire']) ? DateTime::create($options['expire'])->getTimestamp() : null,
            'start_date'  => isset($options['start_date']) ? DateTime::create($options['start_date'])->getTimestamp() : null,
            'end_date'    => isset($options['end_date']) ? DateTime::create($options['end_date'])->getTimestamp() : null,
            'status'      => $options['status'] ?? AwardEnrolmentStatuses::IN_PROGRESS,
            'quantity'    => $options['quantity'] ?? 0,
            'data'        => $data,
            'created'     => isset($options['created']) ? DateTime::create($options['created'])->getTimestamp() : time(),
            'updated'     => isset($options['updated']) ? DateTime::create($options['updated'])->getTimestamp() : time(),
        ]);

        return $db->lastInsertId('award_enrolment');
    }
}
