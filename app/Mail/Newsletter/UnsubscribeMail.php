<?php

namespace App\Mail\Newsletter;

use App\Newsletter\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UnsubscribeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var mixed
     */
    private $subscriber;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Subscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mails.newsletters.unsubscribe')
            ->subject('Konfirmasi Berhenti Berlangganan Nawala')
            ->with('subscriber', $this->subscriber);
    }
}
