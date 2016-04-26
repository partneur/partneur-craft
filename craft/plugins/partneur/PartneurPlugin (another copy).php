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
        
        
        
        // Support Sprout Forms plugin for surveys
        // when a user completes a survey, save the id of the form so we don't serve them repeats
        craft()->on('sproutForms.saveEntry', function(Event $event) {
           
            // SproutForms_EntryModel
            $formEntry = $event->params['entry'];
            // get the currently active user
            $user = craft()->userSession->getUser();

            //add the current formId to the array of completedSurveys for the user
            $surveys = $user->getContent()->completedSurveys;
            //set a default for the first time when the surveys are null
            if ($surveys == null) $surveys = [];
            $new_survey['col1'] = $formEntry->formId;
            array_push($surveys, $new_survey);
            
            //update the user model
            $user->setContentFromPost(array(
                'completedSurveys' => $surveys,
            ));
/*            
            echo('<pre>');
            var_dump($user->getContent()->completedSurveys);
            echo('</pre>');
            die();
*/
            // save the user with the new survey
            craft()->users->saveUser($user);
        });

        
        // on a profile edit by the user from the front end
        // if there are new skills, save the skill entries and capture the reference id
        // organize the skills from the form to update the matrix fields correctly
        craft()->on('users.onBeforeSaveUser', function(Event $event) {
            
            // check for the 'fef' (front end form) parameter so the backend doesn't trigger this
            if (isset($_POST['fef']) && !$event->params['isNewUser'] && $_POST['fef'] ) { 
             
                $user = craft()->users->getUserById($event->params['user']['id']);
              
                // Grab the new data that was posted
                $updated_fields = craft()->request->post['fields'];
                
                
                // skill deletion/repetition is handled with js, so just save the skill ids array             
                $updated_favorite_ids =  array();
                $updated_regular_ids =  array();
                
                if (isset($updated_fields['userSkills'])){
                    foreach ($updated_fields['userSkills'] as $updated_skill_list)
                    {
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
                
                // ids array is filled so update the user profile                
                $newmatrixdata = array();
                // Replace the user's userSkills matrices with the new ones from above.
                // Note the 'new1' and 'new2' keys for that array
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
        
        });
    }
}


        
       
