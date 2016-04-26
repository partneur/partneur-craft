<?php
namespace Craft;

class PartneurVariable 
{
    public function markMessageRead($id = null)
    {
        craft()->partneur_message->markMessageRead($id);
        return;
    }

    
}
