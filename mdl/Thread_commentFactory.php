<?php

namespace dokuwiki\plugin\bez\mdl;

class Thread_commentFactory extends Factory {

    protected function select_query() {
        return "SELECT thread_comment.*, thread.coordinator
                FROM thread_comment JOIN thread ON thread_comment.thread_id=thread.id";
    }

    public function get_from_thread(Thread $thread) {
        return $this->get_all(array('thread_id' => $thread->id), $orderby='', $desc=true, array('thread' => $thread));
    }

    /**
     * @param Thread_comment $thread_comment
     * @param                $data
     * @throws \Exception
     */
    public function initial_save(Entity $thread_comment, $data) {
        parent::initial_save($thread_comment, $data);

        $thread_comment->set_data($data);
        try {
            $this->beginTransaction();
            $this->save($thread_comment);

            $thread_comment->thread->set_participant_flags($thread_comment->author, array('subscribent', 'commentator'));
            $thread_comment->thread->update_last_activity();

            $this->commitTransaction();
        } catch(Exception $exception) {
            $this->rollbackTransaction();
        }

        $thread_comment->mail_notify_add();
    }

    public function update_save(Entity $thread_comment, $data) {
        parent::update_save($thread_comment, $data);

        $thread_comment->set_data($data);
        try {
            $this->beginTransaction();
            $this->save($thread_comment);

            $thread_comment->thread->update_last_activity();

            $this->commitTransaction();
        } catch(Exception $exception) {
            $this->rollbackTransaction();
        }
    }

    public function delete(Entity $obj) {
        try {
            $this->beginTransaction();

            parent::delete($obj);
            $obj->thread->update_last_activity();

            $this->commitTransaction();
        } catch(Exception $exception) {
            $this->rollbackTransaction();
        }
    }
}