<?php
namespace Craft;

class Partneur_MessageService extends BaseApplicationComponent
{
    public function markMessageRead ($id)
    {
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->section = 'messages';
        $criteria->id   = $id;
        $entries = $criteria->find();
        $entries[0]->getContent()->setAttributes(array(
            'read' => true,
        ));
        $success = craft()->entries->saveEntry($entries[0]);
        return $success;
    }

}
