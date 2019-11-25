<?php

namespace Froakie\Listeners;

use Carbon\Carbon;
use Froakie\Components\CRM\CrmFactory;
use Froakie\DTOs\Policy;
use Froakie\Events\PolicyIncoming;
use Froakie\Services\LeadsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class UploadPolicy
 *
 * @package Froakie\Listeners
 * @author Miguel Borges <miguel.borges@edirectinsure.com>
 */
class UploadPolicy implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 180;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 20;

    /**
     * @var \Froakie\Services\LeadsService
     */
    protected $leadsService;

    /**
     * Create the event listener.
     *
     * @param \Froakie\Services\LeadsService $leadsService
     */
    public function __construct(LeadsService $leadsService)
    {
        $this->leadsService = $leadsService;
    }

    /**
     * Handle the event.
     *
     * @param \Froakie\Events\PolicyIncoming $event
     * @throws \Exception
     */
    public function handle(PolicyIncoming $event)
    {
        try {
            app('log')->debug('UploadPolicy listener has catch a PolicyIncoming Event', ['event' => $event]);
            $crm = CrmFactory::getInstance()->getCRMLeadAdapter($event->crm);
            $crm->uploadPolicy($event->policy, $event->policyType, $event->lead, $event->options);

            app('log')->info(
                "A policy has been uploaded to lead {$event->lead->reference} in {$event->crm}",
                [
                    'reference' => $event->lead->reference,
                ]
            );
        } catch (\Exception $exception) {
            $status = Policy::API_STATUS_DOWNLOAD_POLICY_RETRING;
            if ($this->attempts() >= $this->job->maxTries()) {
                $status = Policy::API_STATUS_DOWNLOAD_POLICY_FAILED;
            }
            $crm->updatePolicyAPIStatus($status, $event->lead, $event->options['personNumber']);
            app('log')->error("A error occurred on send policy of lead {$event->lead->reference} in {$event->crm}", [
                'event' => $event,
                'exception' => $exception,
            ]);

            if ($this->attempts() < $this->job->maxTries()) {
                $this->release(Carbon::now()->addMinutes(fib($this->attempts())));
            } else {
                throw $exception;
            }
        }
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return Carbon::now()->addMinutes(3);
    }
}
