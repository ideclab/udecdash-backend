<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CourseUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "Mailable exampleee";

    public function __construct(string $user_name, string $course_name) {
        $this->user_name = $user_name;
        $this->course_name = $course_name;
    }

    public function build() {
        return $this->view('Mailable.CourseUpdated')->with(
            ['user_name' => $this->user_name, 'course_name' => $this->course_name]
        );
    }
}
