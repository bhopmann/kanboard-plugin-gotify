<?php

namespace Kanboard\Plugin\Gotify\Notification;

require_once(__DIR__.'/../vendor/autoload.php');

use Kanboard\Core\Base;
use Kanboard\Core\Notification\NotificationInterface;
use Kanboard\Model\TaskModel;
use Kanboard\Model\SubtaskModel;
use Kanboard\Model\CommentModel;
use Kanboard\Model\TaskFileModel;
use League\HTMLToMarkdown\HtmlConverter;

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
     * @param  string    $event_name
     * @param  array     $event_data
     */
    public function notifyUser(array $user, $event_name, array $event_data)
    {
        $gotify_url = $this->userMetadataModel->get($user['id'], 'gotify_url', $this->configModel->get('gotify_url'));
        $gotify_token = $this->userMetadataModel->get($user['id'], 'gotify_token', $this->configModel->get('gotify_token'));
        $gotify_priority = $this->userMetadataModel->get($user['id'], 'gotify_priority', $this->configModel->get('gotify_priority'));

        if (! empty($gotify_url) and ! empty($gotify_token))
        {
            if ($event_name === TaskModel::EVENT_OVERDUE)
            {
                foreach ($event_data['tasks'] as $task)
                {
                    $project = $this->projectModel->getById($task['project_id']);
                    $event_data['task'] = $task;
                    $this->sendMessage($gotify_url, $gotify_token, $gotify_priority, $project, $event_name, $event_data);
                }
            } else
            {
                $project = $this->projectModel->getById($event_data['task']['project_id']);
                $this->sendMessage($gotify_url, $gotify_token, $gotify_priority, $project, $event_name, $event_data);
            }
        }
    }

    /**
     * Send notification to a project
     *
     * @access public
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     */
    public function notifyProject(array $project, $event_name, array $event_data)
    {
      $gotify_url = $this->projectMetadataModel->get($project['id'], 'gotify_url', $this->configModel->get('gotify_url'));
      $gotify_token = "-g".$this->projectMetadataModel->get($project['id'], 'gotify_token', $this->configModel->get('gotify_token'));
      $gotify_priority = $this->projectMetadataModel->get($project['id'], 'gotify_priority', $this->configModel->get('gotify_priority'));

        if (! empty($gotify_url) and ! empty($gotify_token))
        {
            $this->sendMessage($gotify_url, $gotify_token, $gotify_priority, $project, $event_name, $event_data);
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
    * @param  string    $event_name
    * @param  array     $event_data
    */
    protected function sendMessage($gotify_url, $gotify_token, $gotify_priority, array $project, $event_name, array $event_data)
    {
      // Change $gotify_verbose to true while debugging gotify
      $gotify_verbose = false;

      // Get required data
      if ($this->userSession->isLogged())
      {
          $author = $this->helper->user->getFullname();
          $title = $this->notificationModel->getTitleWithAuthor($author, $event_name, $event_data);
      }
      else
      {
          $title = $this->notificationModel->getTitleWithoutAuthor($event_name, $event_data);
      }

      $proj_name = isset($event_data['project_name']) ? $event_data['project_name'] : $event_data['task']['project_name'];
      $task_title = $event_data['task']['title'];
      $task_url = $this->helper->url->to('TaskViewController', 'show', array('task_id' => $event_data['task']['id'], 'project_id' => $project['id']), '', true);

      // Build message

      $message = "[".addslashes($proj_name)."]\n";

      // Add additional informations

      $description_events = array(TaskModel::EVENT_CREATE, TaskModel::EVENT_UPDATE, TaskModel::EVENT_USER_MENTION);
      $subtask_events = array(SubtaskModel::EVENT_CREATE, SubtaskModel::EVENT_UPDATE, SubtaskModel::EVENT_DELETE);
      $comment_events = array(CommentModel::EVENT_UPDATE, CommentModel::EVENT_CREATE, CommentModel::EVENT_DELETE, CommentModel::EVENT_USER_MENTION);

      if (in_array($event_name, $subtask_events))  // For subtask events
      {
          $subtask_status = $event_data['subtask']['status'];
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

          $message .= "\n<b>  ↳ ".$subtask_symbol.'</b> <em>"'.addslashes($event_data['subtask']['title']).'"</em>';
      }

      elseif (in_array($event_name, $description_events))  // If description available
      {
          if ($event_data['task']['description'] != '')
          {
              $message .= "\n✏️ ".'<em>"'.addslashes($event_data['task']['description']).'"</em>';
          }
      }

      elseif (in_array($event_name, $comment_events))  // If comment available
      {
          $message .= "\n💬 ".'<em>"'.addslashes($event_data['comment']['comment']).'"</em>';
      }

      // Add URL

      if ($this->configModel->get('application_url') !== '')
      {
          $message .= '📝 <a href="'.$task_url.'">'.addslashes($task_title).'</a>';
      }
      else
      {
          $message .= addslashes($task_title);
      }


      if (! empty($gotify_url) and ! empty($gotify_token))
      {

      $converter = new HtmlConverter();

      $task = $this->taskFinderModel->getDetails($event_data['task']['id']);

        $data = [
            "title"=> "[$proj_name] $title",
            "message"=> $converter->convert($this->template->render('notification/task_create', array('task' => $task))),
            "priority"=> intval($gotify_priority),
            "extras" => [
            "client::display" => [
                "contentType" => "text/markdown"
            ]
            ]
        ];

$data_string = json_encode($data);

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
          if($gotify_verbose){ echo "Your Message was Submitted"; }
        break;
      case "400":
        if($gotify_verbose){ echo "Bad Request"; }
        break;
      case "401":
        if($gotify_verbose){ echo "Unauthorized Error - Invalid Token"; }
        break;
      case "403":
        if($gotify_verbose){ echo "Forbidden"; }
        break;
      case "404":
        if($gotify_verbose){ echo "API URL Not Found"; }
        break;
      default:
        if($gotify_verbose){ echo "Hmm Something Went Wrong or HTTP Status Code is Missing"; }
}
      }
    }
}
