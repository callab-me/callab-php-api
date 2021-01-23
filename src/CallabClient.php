<?php

/**
 * Class Client
 */
class CallabClient {
    protected $base_url;
    protected $secret;
    protected $access_key;

    private const ACCESS_SUFFIX = '/access/';
    private const CREATE_ROOM = 'room.create';
    private const DELETE_ROOM = 'room.delete';
    private const JOIN_ROOM = 'room.join';
    private const MEETING_SCHEDULE = 'meeting.schedule';
    private const MEETING_INVITE_MAIL = 'meeting.invite.mail';
    private const MEETING_INVITE_USER_ID = 'meeting.invite.userId';

    /**
     * NiceClient constructor.
     * @param $base_url
     * @param $secret
     * @param $access_key
     */
    public function __construct($base_url, $secret, $access_key)
    {
        $this->base_url = $base_url;
        $this->secret = $secret;
        $this->access_key = $access_key;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $title
     * @param $timezone
     * @param null $reminderDate1
     * @param null $reminderDate2
     * @param bool $createTemporaryRoom
     * @param null $roomForUseOrDuplicateId
     * @return mixed
     * @throws Exception
     */
    public function scheduleMeeting($startDate, $endDate, $title, $timezone, $reminderDate1 = null, $reminderDate2 = null, $createTemporaryRoom = true, $roomForUseOrDuplicateId = null) {
        return $this->request(
            self::MEETING_SCHEDULE,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'title' => $title,
                'timezone' => $timezone,
                'reminder1' => $reminderDate1,
                'reminder2' => $reminderDate2,
                'createOwnRoom' => $createTemporaryRoom,
                'roomId' => $roomForUseOrDuplicateId,
            ]
        );
    }

    /**
     * @param $name
     * @param $title
     * @param null $type
     * @param null $brandId
     * @param null $iconsetId
     * @param null $secret
     * @param false $isLocked
     * @param bool $hasChat
     * @param bool $hasFileSharing
     * @param false $hasVisitors
     * @param false $hasVisitorsChat
     * @return mixed
     * @throws Exception
     */
    public function createRoom(
        $name,
        $title,
        $type = null,
        $brandId = null,
        $iconsetId = null,
        $secret = null,
        $isLocked = false,
        $hasChat = true,
        $hasFileSharing = true,
        $hasVisitors = false,
        $hasVisitorsChat = false
    ) {
        return $this->request(
            self::CREATE_ROOM,
            [
                'name' => $name,
                'title' => $title,
                'type' => $type,
                'brandId' => $brandId,
                'iconsetId' => $iconsetId,
                'secret' => $secret,
                'isLocked' => $isLocked,
                'hasChat' => $hasChat,
                'hasFileSharing' => $hasFileSharing,
                'hasVisitors' => $hasVisitors,
                'hasVisitorsChat' => $hasVisitorsChat,
            ]
        );
    }

    /**
     * @param $roomId
     * @return mixed
     * @throws Exception
     */
    public function deleteRoom(
        $roomId
    ) {
        return $this->request(
            self::DELETE_ROOM,
            [
                'id' => $roomId,
            ]
        );
    }

    /**
     * @param $roomName
     * @return mixed
     * @throws Exception
     */
    public function adminJoinToken(
        $roomName
    ) {
        return $this->request(
            self::JOIN_ROOM,
            [
                'roomName' => $roomName,
            ]
        );
    }

    /**
     * @param $meetingId
     * @param $email
     * @return mixed
     * @throws Exception
     */
    public function inviteToMeetingByMail($meetingId, $email) {
        return $this->request(
            self::MEETING_INVITE_MAIL,
            [
                'meetingId' => $meetingId,
                'email' => $email,
            ]
        );
    }

    /**
     * @param $meetingId
     * @param $email
     * @return mixed
     * @throws Exception
     */
    public function inviteToMeetingByUserId($meetingId, $userId) {
        return $this->request(
            self::MEETING_INVITE_USER_ID,
            [
                'meetingId' => $meetingId,
                'userId' => $userId,
            ]
        );
    }

    /**
     * @param $operation
     * @param $payload
     * @return mixed
     * @throws Exception
     */
    protected function request($operation, $payload) {
        $url = $this->base_url . self::ACCESS_SUFFIX . $operation;

        $request = [
            'api_key' => $this->access_key,
            'signature' => $this->sign($payload),
            'body' => json_encode($payload)
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        $response = curl_exec($ch);

        $code =  curl_getinfo($ch, CURLINFO_HTTP_CODE) ?? 0;
        $result = json_decode($response, true);

        curl_close($ch);

        if($code === 200) {
            return $result['data'];
        } else if($code === 403) {
            throw new \Exception('Wrong credentials or user is locked! Don\'t repeat this too often or your IP adress will be blocked.', 403);
        } else if($code === 422) {
            throw new \Exception('Validation error: ' . print_r($result['errors'], true), 422);
        } else if($code === 404) {
            throw new \Exception('Your API key does not allow this operation or it was removed.', 404);
        } else {
            throw new \Exception('Unexpected error! Code ' . $code . ' - ' . $result, 500);
        }
    }

    /**
     * @param $payload
     * @return string
     */
    protected function sign($payload) {
        return sha1(
            $this->access_key. '-'
            . $this->secret . '-'
            . json_encode($payload)
        );
    }
}