<?php
declare (strict_types = 1);

namespace app\listener\api;

class SendMessageListener
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        $this->sendMessage($event['user'], $event['title'], $event['text']);
    }

    /**
     * 给用户发送消息
     *
     * @param $user
     * @param $title
     * @param $text
     */
    public function sendMessage($user, $title, $text)
    {
        $user->messages()->save([
            'uid' => $user->id,
            'title' => $title,
            'texter' => $text,
            'addtime' => gettime()
        ]);
    }
}
