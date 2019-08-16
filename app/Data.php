<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

// use App\Post;

class Data extends Model
{

  protected $primaryKey = 'id';
  protected $fillable = [
    'name',
    'parent_id',
    'parent_type',
    'type',
    'content',
  ];
  public function parent() {
    return $this->morphTo();
  }
  public function children() {
    return $this->morphMany('App\Data', 'parent');
  }

  // public static function ShowID($GroupSig,$PostSig, $DataSig) {
  public static function ShowID($routeParameters,$DataSig) {
    // code...
    $GroupShowID = Group::ShowID($routeParameters);
    $PostShowID = Post::ShowID($GroupShowID,$routeParameters);

    $DataSigFragments = explode("/", $DataSig);
    // ----------------
    // querie strat
    // ----------------

    $stage = 1;
    foreach ($DataSigFragments as $key => $value) {
      if ($stage==1) {
        // code...
        $DataShowSigPref = Data::ShowSignaturePrefix();
        $Post = Post::find($PostShowID);
        if (!empty($Post)) {
          // code...
          $Data = Post::find($PostShowID)->DataChildren->where('name', $DataShowSigPref)->first();
          if (!empty($Data)) {
            // code...
            $ShowID = $Data->id;
            $stage = 2;
          }
        }
      } else {
        array_shift($DataSigFragments);
        $Data = Data::find($ShowID)->children->where('name', $value)->first();

        if (!empty($Data)) {
          $ShowID = $Data->id;
        }
      }




      // $ShowID = DB::select(
      //   'SELECT *
      //   FROM data a, data b
      //   WHERE a.parent_id = b.id
      //   AND a.parent_type = "App\\Data"
      //   ;'
      // );

      // ----------------
      // querie strat
      // ----------------
    }
    if (isset($ShowID)) {
      return $ShowID;
    }



  }

  public static function Show($DataShowID) {
    $DataShowAll = Data::find($DataShowID);
    // dd($dd);
    if (!empty($DataShowAll)) {
      $ShowDataContent = $DataShowAll->toArray();
      // code...

      $Attr = Data::ShowAttributeTypes();

      switch ($ShowDataContent["type"]) {
        case 'text':
        // code...

        $result[$Attr[2]] = $ShowDataContent["content"];
        $result[$Attr[1]] = $ShowDataContent["type"];
        $result[$Attr[0]] = $ShowDataContent["name"];
        $result[$Attr[4]] = $ShowDataContent["id"];
        $result[$Attr[5]] = $ShowDataContent["subtype"];
        break;
        case 'image':
        // code...

        // mime_content_type($DataLocation) == "image/jpeg"

        // $subtype = $ShowDataContent["subtype"];
        // $data = ;
        // $base64 = 'data:image/' . $subtype . ';base64,' . $data;

        $result[$Attr[2]] = $ShowDataContent["content"];
        $result[$Attr[1]] = $ShowDataContent["type"];
        $result[$Attr[0]] = $ShowDataContent["name"];
        $result[$Attr[4]] = $ShowDataContent["id"];
        $result[$Attr[5]] = $ShowDataContent["subtype"];
        break;

        default:
        // code...
        $result[$Attr[2]] = "unknown data type \"".$ShowDataContent["type"]."\"";
        $result[$Attr[1]] = 'text';
        $result[$Attr[0]] = $ShowDataContent["name"];
        $result[$Attr[4]] = $ShowDataContent["id"];
        $result[$Attr[5]] = $ShowDataContent["subtype"];
        break;
      }
      return $result;
    }


  }
  public static function ShowRelativeSignature($arg) {
      $result = Data::ShowSignaturePrefix()."/".$arg;
      return $result;

  }
  public static function ShowSignaturePrefix() {

      $result = "_data";
      return $result;

  }
  public static function ShowAll($routeParameters) {

    if(!function_exists('App\ShowHelper')){
      function ShowHelper($Data,$Identifier) {
        $result = array();
        $Attr = Data::ShowAttributeTypes();

        $Identifier = -1;
        foreach ($Data as $key => $value) {

          $Identifier = $Identifier+1;

          $SubData = Data::find($value["id"])->children->toArray();

          // $DataLocation = $PostShowID . "/" . $value;
          // $result[$value["name"]]["?"] = "?";

          if (!empty($SubData)) {
            // dd($SubData);


            $result[$Identifier][$Attr[2]] = ShowHelper($SubData,$Identifier);
            $result[$Identifier][$Attr[1]] = $value["type"];
            $result[$Identifier][$Attr[0]] = $value["name"];
            $result[$Identifier][$Attr[4]] = $value["id"];
          } else {
            $result[$Identifier] = Data::Show($value["id"]);
          }

        }
        return  $result;
      }
    }
    // dd(1);
    // $PostShowID = PostM::ShowLocation($PostShowID)."/".$ShowDataID;

    $GroupShowID = Group::ShowID($routeParameters);
    $PostShowID = Post::ShowID($GroupShowID,$routeParameters);
    // $PostShowID = Post::ShowID($PostShowID,$routeParameters);
    // dd($PostShowID);
    if (!empty($PostShowID)) {
      // $Show[$ShowDataID] =   ShowHelper($PostShowID);

      $BaseData = Post::find($PostShowID)->DataChildren->toArray();
      $Identifier = null;
      $Show =   ShowHelper($BaseData,$Identifier);
      // dd($Show);
      // dd($Show);
      return $Show;
    }
  }

  public static function ShowAttributeTypes() {
    $ShowAttributeTypes = array  (
      '0'=>'Name',
      '1'=>'Type',
      '2'=>'Content',
      '3'=>'Selected',
      '4'=>'ID',
      '5'=>'Subtype',

    );
    // ["/SmartDataName"] =   'SmartDataName';
    // ["/SmartDataContent"] =   'SmartDataContent';

    return $ShowAttributeTypes;
  }
  public static function ShowActions() {
    $ShowActions["SelectedSmartDataItem"] =   'Selected';
    return $ShowActions;
  }


  public static function Store($request, $ShowID) {
    function StoreHelperStore($Selected,$Data,$Attr) {
      foreach($Data["Content"] as $key => $value) {
        $key = SmartDataItemM::g_base64_decode($key);
        if ($value[$Attr[1]]=="folder"){
          if (isset($value[$Attr[3]]) OR $Selected == 1) {
            if (!empty($value[$Attr[4]])) {
              // code...
              Data::find($value[$Attr[4]])
              ->update([
                'name'=>$value[$Attr[0]],
              ]);
            }

           $Selected = 1;
          } else {
            $Selected = 0;
          }
          StoreHelperStore($Selected, $value,$Attr);
        } else {

          if (isset($value[$Attr[3]]) OR $Selected == 1) {
            // if (!isset($value[$Attr[2]])) {
            //   // code...
            //   dd($value);
            // }
             Data::find($value[$Attr[4]])
             ->update([
               'name'=>$value[$Attr[0]],
               'content'=>$value[$Attr[2]],
             ]);
          }
        }
      }
    }
    $Attr = Data::ShowAttributeTypes();
    // $Data[$Attr[2]][0] = $request->get("Data");
    $Data = $request->get("Data");
    // dd($Data);

    StoreHelperStore(0,$Data,$Attr);
  }

}