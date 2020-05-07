<?php

namespace Kanboard\Plugin\Gotify\Notification;

use Kanboard\Core\Base;
use Kanboard\Core\Notification\NotificationInterface;
use Kanboard\Model\TaskModel;
use Kanboard\Model\SubtaskModel;
use Kanboard\Model\CommentModel;
use Kanboard\Model\TaskFileModel;

/**
 * Gotify Notification
 *
 * @package  Kanboard\Plugin\Gotify
 * @author   Benedikt Hopmann
 */

// Overloaded classes

class Gotify extends Base implements NotificationInterface
{

    /**
     * Send notification to a user
     *
     * @access public
     * @param  array     $user
     * @param  string    $eventName
     * @param  array     $eventData
     */
    public function notifyUser(array $user, $eventName, array $eventData)
    {
        $gotify_url = $this->userMetadataModel->get($user['id'], 'gotify_url', $this->configModel->get('gotify_url'));
        $gotify_token = $this->userMetadataModel->get($user['id'], 'gotify_token', $this->configModel->get('gotify_token'));
        $gotify_priority = $this->userMetadataModel->get($user['id'], 'gotify_priority', $this->configModel->get('gotify_priority'));


        if (! empty($gotify_url) and ! empty($gotify_token))
        {
            if ($eventName === TaskModel::EVENT_OVERDUE)
            {
                foreach ($eventData['tasks'] as $task)
                {
                    $project = $this->projectModel->getById($task['project_id']);
                    $eventData['task'] = $task;
                    $this->sendMessage($gotify_url, $gotify_token, $gotify_priority, $project, $eventName, $eventData);
                }
            } else
            {
                $project = $this->projectModel->getById($eventData['task']['project_id']);
                $this->sendMessage($gotify_url, $gotify_token, $gotify_priority, $project, $eventName, $eventData);
            }
        }
    }

    /**
     * Send notification to a project
     *
     * @access public
     * @param  array     $project
     * @param  string    $eventName
     * @param  array     $eventData
     */
    public function notifyProject(array $project, $eventName, array $eventData)
    {
      $gotify_url = $this->projectMetadataModel->get($project['id'], 'gotify_url', $this->configModel->get('gotify_url'));
      $gotify_token = "-g".$this->projectMetadataModel->get($project['id'], 'gotify_token', $this->configModel->get('gotify_token'));
      $gotify_priority = $this->projectMetadataModel->get($project['id'], 'gotify_priority', $this->configModel->get('gotify_priority'));

        if (! empty($gotify_url) and ! empty($gotify_token))
        {
            $this->sendMessage($gotify_url, $gotify_token, $gotify_priority, $project, $eventName, $eventData);
        }
    }

    /**
    * Send message to Gotify
    *
    * @access private
    * @param  string    $gotify_url
    * @param  string    $gotify_token
    * @param  string    $gotify_priority
    * @param  array     $project
    * @param  string    $eventName
    * @param  array     $eventData
    */
    protected function sendMessage($gotify_url, $gotify_token, $gotify_priority, array $project, $eventName, array $eventData)
    {
      // Get required data

      if ($this->userSession->isLogged())
      {
          $author = $this->helper->user->getFullname();
          $title = $this->notificationModel->getTitleWithAuthor($author, $eventName, $eventData);
      }
      else
      {
          $title = $this->notificationModel->getTitleWithoutAuthor($eventName, $eventData);
      }

      $proj_name = isset($eventData['project_name']) ? $eventData['project_name'] : $eventData['task']['project_name'];
      $task_title = $eventData['task']['title'];
      $task_url = $this->helper->url->to('TaskViewController', 'show', array('task_id' => $eventData['task']['id'], 'project_id' => $project['id']), '', true);

      // Build message

      $message = "[".addslashes($proj_name)."]\n";
      $message .= addslashes($title)."\n";

      // Add additional informations

      $description_events = array(TaskModel::EVENT_CREATE, TaskModel::EVENT_UPDATE, TaskModel::EVENT_USER_MENTION);
      $subtask_events = array(SubtaskModel::EVENT_CREATE, SubtaskModel::EVENT_UPDATE, SubtaskModel::EVENT_DELETE);
      $comment_events = array(CommentModel::EVENT_UPDATE, CommentModel::EVENT_CREATE, CommentModel::EVENT_DELETE, CommentModel::EVENT_USER_MENTION);

      if (in_array($eventName, $subtask_events))  // For subtask events
      {
          $subtask_status = $eventData['subtask']['status'];
          $subtask_symbol = '';

          if ($subtask_status == SubtaskModel::STATUS_DONE)
          {
              $subtask_symbol = '❌ ';
          }
          elseif ($subtask_status == SubtaskModel::STATUS_TODO)
          {
              $subtask_symbol = '';
          }
          elseif ($subtask_status == SubtaskModel::STATUS_INPROGRESS)
          {
              $subtask_symbol = '🕘 ';
          }

          $message .= " ↳ ".$subtask_symbol . addslashes($eventData['subtask']['title'])."\n";
      }

      elseif (in_array($eventName, $description_events))  // If description available
      {
          if ($eventData['task']['description'] != '')
          {
              $message .= "✏️ ".addslashes($eventData['task']['description'])."\n";
          }
      }

      elseif (in_array($eventName, $comment_events))  // If comment available
      {
          $message .= "💬 ".addslashes($eventData['comment']['comment'])."\n";
      }

      // Add URL

      if ($this->configModel->get('application_url') !== '')
      {
          $message .= "📝 ".addslashes($task_title) . ": ".$task_url;
      }
      else
      {
          $message .= addslashes($task_title);
      }

      if (! empty($gotify_url) and ! empty($gotify_token))
      {

  $data = [
    #"title"=> "Hello World",
    "message"=> "$message",
    "priority"=> 5
    #"extras" => [
    #"client::display" => [
    #    "contentType" => "text/markdown"
    #]
    #]
];

#$data_string = json_encode($data);

$data_string = "{\"message\":".json_encode($message).",\"priority\":$gotify_priority}";

$url = "$gotify_url/message?token=$gotify_token";

$headers = [
    "Content-Type: application/json; charset=utf-8"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

$result = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close ($ch);

switch ($code) {
    case "200":
        echo "<strong>Your Message was Submitted</strong>";
        break;
    case "400":
        echo "<strong>Bad Request</strong>";
        break;
    case "401":
        echo "<strong>Unauthorized Error - Invalid Token</strong>";
        break;
    case "403":
        echo "<strong>Forbidden</strong>";
        break;
    case "404":
        echo "<strong>API URL Not Found</strong>";
        break;
    default:
        echo "<strong>Hmm Something Went Wrong or HTTP Status Code is Missing</strong>";
}
      }
    }
}
