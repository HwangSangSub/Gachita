<?php
require '../vendor/autoload.php';

// ini_set("display_errors", 1);
// ini_set("track_errors", 1);
// ini_set("html_errors", 1);
// error_reporting(E_ALL);

use Google\Cloud\Firestore\FirestoreClient;

function fire_Get($dbname, $idx)
{
    // Firestore 클라이언트 객체 생성
    $firestore = new FirestoreClient([
        'projectId' => 'gachi-5246d',
    ]);
    // "chat" 컬렉션의 모든 문서 가져오기
    $collection = $firestore->collection($dbname)->document($idx);
    $documents = $collection->snapshot();

    if ($documents->exists()) {
        //firestore에서 필드값 가져오기.
        $data = $documents->data();
    } else {
        $data = "";
    }
    // $documents = $collection->documents();

    // foreach ($documents as $document) {
    //     // 문서 데이터 가져오기
    //     $data = $document->data();
    //     print_r($data);
    //     // 데이터 처리
    //     // ...
    // }
    return $data;
}

//양도가 완료된 경우 파이어스토어에 chat > 노선생성고유번호 > 필드값 : complete : true 추가하기.
function fire_Complete_Set($idx)
{
    // Firestore 클라이언트 객체 생성
    $firestore = new FirestoreClient([
        'projectId' => 'gachi-5246d',
    ]);

    // 업데이트할 문서의 참조 가져오기
    $documentRef = $firestore->collection('chat')->document($idx);
    // 업데이트할 필드와 값 지정
    $documentRef->set([
        'complete' => true
    ], ['merge' => true]);
}

/**
 * 내좌표를 등록하고 상대방 좌표받기
 *
 * @param [int] $idx
 * @param [string] $type (maker, together)
 * @param [double] $lng
 * @param [double] $lat
 * @return void
 */
function fire_Locat_Set($idx, $type, $lng, $lat)
{
    // Firestore 클라이언트 객체 생성
    $firestore = new FirestoreClient([
        'projectId' => 'gachi-5246d',
    ]);

    // 업데이트할 문서의 참조 가져오기
    $documentRef = $firestore->collection('chat')->document($idx);
    $geoPoint = new \Google\Cloud\Core\GeoPoint($lat, $lng);
    // 업데이트할 필드와 값 지정
    if ($type == 'maker') {
        $documentRef->set([
            'maker' => $geoPoint
        ], ['merge' => true]);
    } else if ($type == 'together') {
        $documentRef->set([
            'together' => $geoPoint
        ], ['merge' => true]);
    }else{
        return false;   
    }
}
