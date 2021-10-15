<?php
namespace App\Classes\InteractionsDiscovery;

use Illuminate\Support\Facades\Log;
use App\Classes\DataStructure\Identifier;
use App\Classes\InteractionsDiscovery\Interaction;
use App\Classes\InteractionsDiscovery\InteractionResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InteractionDiscovery extends InteractionResolver {

    public function __construct(\stdClass $log, Identifier $courseIdentifier){
        Log::debug('[InteractionDiscovery::class] [construct]');
        parent::__construct($log, $courseIdentifier);
        $this->interaction = new Interaction();
        $this->interaction->log = $this->log;
        if(Interaction::isTrash($this->log)){
            $this->interaction->deleteAssociateLog();
        }else{
            $this->discoveryInteraction();
        }
    }

    private function discoveryInteraction() : void {
        Log::debug('[InteractionDiscovery::class] [discoveryInteraction] STARTING ---------------------------------------------------------');
        Log::debug('[InteractionDiscovery::class] [discoveryInteraction] Course identifier and current log =>', [$this->courseIdentifier, $this->log]);
        $this->setInteractionOwner();
        $this->setInteractionCourse();
        $this->setInteractionDate();
        $this->setInteractionDevice();
        $this->setUrl();
        $this->findAndSetInteractionType();
        Log::debug('[InteractionDiscovery::class] [discoveryInteraction] Interaction builded =>', [$this->interaction]);
        $this->interaction->moveToRespectiveInteractionTable();
    }

    private function setUrl(){
        $this->interaction->url = $this->log->url;
    }

    private function setInteractionOwner() : void {
        $this->interaction->user_id = $this->log->user_id;
    }

    private function setInteractionCourse() : void {
        $this->interaction->course_id = $this->log->course_id;
    }

    private function setInteractionDate() : void {
        $date = $this->unifyStringDate($this->log->timestamp);
        if(is_null($date)){
            Log::info("[InteractionDiscovery::class] [setInteractionDate] Date error",
            [$this->log->timestamp, $date]);
        }
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC');
        $date->setTimezone('America/Santiago');
        $this->interaction->viewed = $date->format('Y-m-d H:i:s');
    }

    private function unifyStringDate($date) : ?string {
        $regex_match = array();
        $pattern = "/[\d][\d][\d][\d]-[\d][\d]-[\d][\d] [\d][\d]:[\d][\d]:[\d][\d]/";
        preg_match($pattern, $date, $regex_match, PREG_OFFSET_CAPTURE);
        $date = isset($regex_match[0][0]) ? $regex_match[0][0] : null;
        return $date;
    }

    private function setInteractionDevice() : void {
        $device = null;
        $pattern = "/Mobile|iP(hone|od|ad)|Android|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|(hpw|web)OS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/";
        $isMobileDevice = preg_match($pattern, $this->log->user_agent);
        if($isMobileDevice){
            $device = 'MOBILE';
        }else{
            $device = 'DESKTOP';
        }
        Log::debug("[InteractionDiscovery::class] [setInteractionDevice] The next user agent was categorized like {$device}", [$this->log->user_agent]);
        $this->interaction->device = $device;
    }

    private function setInteractionItemId(?Identifier $identifier) : void {
        if(empty($identifier)){
            Log::info('[InteractionDiscovery::class] [setInteractionItemId] Interaction was setted with null identifier for relationated item.', [$this->interaction]);
        }else{
            $this->interaction->item_id = $identifier->id;
            $this->interaction->item_canvas_id = $identifier->canvas_id;
        }
    }

    private function setInteractionType(string $type) : void {
        $this->interaction->type = $type;
    }

    private function findAndSetInteractionType() : void {
        $interactionResolvers = $this->interactionResolvers();
        foreach($interactionResolvers as $interactionType => $resolver){
            Log::debug("[InteractionDiscovery::class] [findAndSetInteractionType] Cheking $interactionType Type with the URL =>", [$this->log->url]);
            $function = "App\\Classes\\InteractionsDiscovery\\InteractionDiscovery::$resolver";
            $resolverResponse = call_user_func_array($function, []);
            if(!empty($resolverResponse)){
                $this->setInteractionType($interactionType);
                $this->setInteractionItemId($resolverResponse);
                break;
            }
        }
    }
}
