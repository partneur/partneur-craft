<?php
namespace Craft;


class PartneurPlugin extends BasePlugin
{
        
    function getName()
    {
         return Craft::t('Partneur');
    }

    function getVersion()
    {
        return '0.7';
    }

    function getDeveloper()
    {
        return 'Tech Gents';
    }

    function getDeveloperUrl()
    {
        return 'tech-gents.com';
    }
    
    function init()
    {
        parent::init();
        
        // So all this works, still needs to have error checking and logging though
        craft()->on('users.onBeforeSaveUser', function(Event $event) {
            
            // the 'fef' parameter isn't set on the the save user link on the backend edit profile, so check for it
            if (isset($_POST['fef']) && !$event->params['isNewUser'] && $_POST['fef'] ) { //only run through this for existing users that are being saved from the front end form
             
                $user = craft()->users->getUserById($event->params['user']['id']);
              
                // Grab the new data that was posted
                $updated_fields = craft()->request->post['fields'];
                
                //echo('<pre>');
                //var_dump( $updated_fields);
                //echo('</pre>');
                 
                
                // The deleting of skills will be handled with javascript on the front end, so the array of skill ids that gets here will be what needs to be saved
                // There's a possibility of leaving orphan skill entries, but those can be searched for and disabled by admins periodically
                // Also check if skill already exists with javascript, if so add it to the relevant 'skills' list (not the 'newskills' list) so it's included in the post data
                
                $updated_favorite_ids =  array();
                $updated_regular_ids =  array();
                
                if (isset($updated_fields['userSkills'])){
                    foreach ($updated_fields['userSkills'] as $updated_skill_list)
                    {
                        //echo $updated_skill_list['type'] . ' ids: ' . join(',',$updated_skill_list['fields']['skills']);
                        if(isset($updated_skill_list['fields'])){
                            // First grab all the existing skills and put them in the appropriate array (of ids)
                            if ($updated_skill_list['type'] == 'favoriteskills')
                            {
                                $updated_favorite_ids = $updated_skill_list['fields']['skills'];
                            }
                            elseif ($updated_skill_list['type'] == 'regularskills')
                            {
                                $updated_regular_ids = $updated_skill_list['fields']['skills'];   
                            }
                        }
                        // Then create the new skill entries (if they exist)
                        if (isset($updated_skill_list['fields']['newskills'])) {
                            foreach($updated_skill_list['fields']['newskills'] as $newskill)
                            {
                                $entry = new EntryModel();
                                // Hardcoded for skills:
                                $entry->sectionId = 3; 
                                $entry->typeId    = 3;
                                
                                $entry->authorId  = $user['id'];
                                $entry->getContent()->title = $newskill;

                                $entry->getContent()->approved = true;
                                $entry->enabled = true;


                                $success = craft()->entries->saveEntry($entry);

                                if ($success)
                                {
                                    //echo "New skill: ".$newskill." created. Id: ".$entry->id."<br>";
                                    PartneurPlugin::log("New skill: ".$newskill." created. Id: ".$entry->id.". For user: ".$user);
                                    
                                    // Then push the new id on the end of the appropriate array
                                    if ($updated_skill_list['type'] == 'favoriteskills')
                                    {
                                        $updated_favorite_ids[] = $entry->id;
                                    }
                                    elseif ($updated_skill_list['type'] == 'regularskills')
                                    {
                                        $updated_regular_ids[] = $entry->id;   
                                    }

                                }
                                else
                                {
                                    // handle error   
                                }
                            }
                        }
                    }
                }
                
                // So now each updated ids array has been filled and the new skills have been created, time to update the user profile:
                
                $newmatrixdata = array();
                
                // Replace the user's userSkills matrices with the new ones from above. Note the 'new1' and 'new2' keys for that array
                $newmatrixdata['new1'] = array(
                    'type' => 'favoriteskills',
                    'enabled' => true,
                    'fields' => array(
                        'skills' => $updated_favorite_ids
                    )
                );
                
                $newmatrixdata['new2'] = array(
                    'type' => 'regularskills',
                    'enabled' => true,
                    'fields' => array(
                        'skills' => $updated_regular_ids
                    )
                );
                
               
                $user->setContentFromPost(array('userSkills' => $newmatrixdata));
               
                // Craft handles the saving the updated user model after this and will redirect to the profile page
             
                
            }
            
           // die(); 
        
        });
    }
}


        
       
