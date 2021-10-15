<?php
    namespace App\Classes;

    class ResourceType {
        public const WIKI_PAGE = 'WikiPage';
        public const QUIZ = 'Quiz';
        public const ATTACHMENT = 'Attachment';
        public const DISCUSSION_TOPIC = 'DiscussionTopic';
        public const ASSIGNMENT = 'Assignment';
        public const EXTERNAL_URL = 'ExternalUrl';
        public const CONTEXT_EXTERNAL_TOOL = 'ContextExternalTool';

        public static function all(){
            return [self::WIKI_PAGE, self::QUIZ, self::ATTACHMENT,
            self::DISCUSSION_TOPIC, self::ASSIGNMENT, self::EXTERNAL_URL,
            self::CONTEXT_EXTERNAL_TOOL];
        }
    }

