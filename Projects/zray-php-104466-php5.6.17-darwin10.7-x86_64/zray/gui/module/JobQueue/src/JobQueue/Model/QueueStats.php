<?php
/**
 * Queue statistics container 
 */

namespace JobQueue\Model;

class QueueStats {

    protected $queueId;
    protected $runningJobsCount = 0;
    protected $pendingJobsCount = 0;
    
    public function __construct(array $queueStats = array()) {
        $this->setQueueId($queueStats['queue_id']);
        $this->setRunningJobsCount($queueStats['running_jobs_count']);
        $this->setPendingJobsCount($queueStats['pending_jobs_count']);
    }
    
    /**
     * @return the $queueId
     */
    public function getQueueId()
    {
        return $this->queueId;
    }

    /**
     * @return the $runningJobsCount
     */
    public function getRunningJobsCount()
    {
        return $this->runningJobsCount;
    }

    /**
     * @return the $pendingJobsCount
     */
    public function getPendingJobsCount()
    {
        return $this->pendingJobsCount;
    }

    /**
     * @param field_type $queueId
     */
    public function setQueueId($queueId)
    {
        $this->queueId = $queueId;
    }

    /**
     * @param field_type $runningJobsCount
     */
    public function setRunningJobsCount($runningJobsCount)
    {
        $this->runningJobsCount = $runningJobsCount;
    }

    /**
     * @param field_type $pendingJobsCount
     */
    public function setPendingJobsCount($pendingJobsCount)
    {
        $this->pendingJobsCount = $pendingJobsCount;
    }

    
    
}

