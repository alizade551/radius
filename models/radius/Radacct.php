<?php

namespace app\models\radius;


use Yii;
use yii\db\Expression;


/**
 * This is the model class for table "radacct".
 *
 * @property int $radacctid
 * @property string $acctsessionid
 * @property string $acctuniqueid
 * @property string $username
 * @property string $realm
 * @property string $nasipaddress
 * @property string $nasportid
 * @property string $nasporttype
 * @property string $acctstarttime
 * @property string $acctupdatetime
 * @property string $acctstoptime
 * @property int $acctinterval
 * @property int $acctsessiontime
 * @property string $acctauthentic
 * @property string $connectinfo_start
 * @property string $connectinfo_stop
 * @property int $acctinputoctets
 * @property int $acctoutputoctets
 * @property string $calledstationid
 * @property string $callingstationid
 * @property string $acctterminatecause
 * @property string $servicetype
 * @property string $framedprotocol
 * @property string $framedipaddress
 * @property string $framedipv6address
 * @property string $framedipv6prefix
 * @property string $framedinterfaceid
 * @property string $delegatedipv6prefix
 */
class Radacct extends \yii\db\ActiveRecord
{
    public $startDate;
    public $endDate;


    const IP_HISTORY = 'ip_history';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::IP_HISTORY] = ['startDate','endDate'];

        return $scenarios;
    }


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radacct';
    }



    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['startDate','endDate'], 'required' , 'on'=>'ip_history'],
            [['acctstarttime', 'acctupdatetime', 'acctstoptime'], 'safe'],
            [['acctinterval', 'acctsessiontime', 'acctinputoctets', 'acctoutputoctets'], 'integer'],
            [['acctsessionid', 'username', 'realm'], 'string', 'max' => 64],
            [['acctuniqueid', 'nasportid', 'nasporttype', 'acctauthentic', 'acctterminatecause', 'servicetype', 'framedprotocol'], 'string', 'max' => 32],
            [['nasipaddress', 'framedipaddress'], 'string', 'max' => 15],
            [['connectinfo_start', 'connectinfo_stop', 'calledstationid', 'callingstationid'], 'string', 'max' => 50],
            [['framedipv6address', 'framedipv6prefix', 'delegatedipv6prefix'], 'string', 'max' => 45],
            [['framedinterfaceid'], 'string', 'max' => 44],
            [['acctuniqueid'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'radacctid' => Yii::t('app', 'Radacctid'),
            'acctsessionid' => Yii::t('app', 'Acctsessionid'),
            'acctuniqueid' => Yii::t('app', 'Acctuniqueid'),
            'username' => Yii::t('app', 'Username'),
            'realm' => Yii::t('app', 'Realm'),
            'nasipaddress' => Yii::t('app', 'Nasipaddress'),
            'nasportid' => Yii::t('app', 'Nasportid'),
            'nasporttype' => Yii::t('app', 'Nasporttype'),
            'acctstarttime' => Yii::t('app', 'Acctstarttime'),
            'acctupdatetime' => Yii::t('app', 'Acctupdatetime'),
            'acctstoptime' => Yii::t('app', 'Acctstoptime'),
            'acctinterval' => Yii::t('app', 'Acctinterval'),
            'acctsessiontime' => Yii::t('app', 'Acctsessiontime'),
            'acctauthentic' => Yii::t('app', 'Acctauthentic'),
            'connectinfo_start' => Yii::t('app', 'Connectinfo Start'),
            'connectinfo_stop' => Yii::t('app', 'Connectinfo Stop'),
            'acctinputoctets' => Yii::t('app', 'Acctinputoctets'),
            'acctoutputoctets' => Yii::t('app', 'Acctoutputoctets'),
            'calledstationid' => Yii::t('app', 'Calledstationid'),
            'callingstationid' => Yii::t('app', 'Callingstationid'),
            'acctterminatecause' => Yii::t('app', 'Acctterminatecause'),
            'servicetype' => Yii::t('app', 'Servicetype'),
            'framedprotocol' => Yii::t('app', 'Framedprotocol'),
            'framedipaddress' => Yii::t('app', 'Framedipaddress'),
            'framedipv6address' => Yii::t('app', 'Framedipv6address'),
            'framedipv6prefix' => Yii::t('app', 'Framedipv6prefix'),
            'framedinterfaceid' => Yii::t('app', 'Framedinterfaceid'),
            'delegatedipv6prefix' => Yii::t('app', 'Delegatedipv6prefix'),
            'startDate' => Yii::t('app', 'Başlangıç tarixi'),
            'endDate' => Yii::t('app', 'Bitiş tarixi'),
        ];
    }

    /**
     * Returns the online status of the user with the given username.
     *
     * @param string $username The username to check online status for.
     * @return bool True if the user is online, false otherwise.
     */
    public static function isUserOnline( $username )
    {
        $latestRecord = self::find()
            ->where(['username' => $username])
            ->orderBy(['radacctid' => SORT_DESC])
            ->one();

        return ($latestRecord !== null && $latestRecord->acctstoptime === null);
    }


    public static function getUserTrafficLastMonth( $username )
    {
        $lastMonth = new Expression('DATE_SUB(NOW(), INTERVAL 1 MONTH)');

        $query = Radacct::find()
            ->select([
                'username',
                'SUM(acctinputoctets) AS total_upload',
                'SUM(acctoutputoctets) AS total_download'
            ])
            ->where(['username' => $username])
            ->andWhere(['>=', 'acctstarttime', $lastMonth])
            ->groupBy('username');

        $result = $query->asArray()->one();

        if ($result === null) {
            $result = [
                'total_download' => 0,
                'total_upload' => 0,
            ];
        } else {
            // Byte to MB conversion
            $result['total_download'] = round ($result['total_download'] / (1024 * 1024),2);
            $result['total_upload'] = round ($result['total_upload'] / (1024 * 1024),2);
        }

        return $result;
    }




    public static function getAcctSessionInfoByUsername($username)
    {
        $latestRecord = Radacct::find()
            ->select(['nasipaddress', 'framedipaddress', 'acctsessiontime','acctinputoctets','acctoutputoctets','callingstationid'])
            ->where(['username' => $username])
            ->orderBy(['radacctid' => SORT_DESC])
            ->one();

        if ($latestRecord) {
             $sessionTime = self::formatAcctSessionTime($latestRecord->acctsessiontime);

            return [
                'download' => round ( $latestRecord->acctoutputoctets / (1024 * 1024), 2 ),
                'upload' => round ( $latestRecord->acctinputoctets / (1024 * 1024), 2 ),
                'nasipaddress' => $latestRecord->nasipaddress,
                'framedipaddress' => $latestRecord->framedipaddress,
                'acctsessiontime' => $sessionTime,
                'mac_address' => $latestRecord->callingstationid,
          
            ];
        } else {
            return [
                'download' => 0,
                'upload' => 0,
                'nasipaddress' => null,
                'framedipaddress' => null,
                'acctsessiontime' => 0,
                'mac_address' => Yii::t('app','Data not set'),
            ];
        }
    }



public static function formatAcctSessionTime($seconds)
{
    $weeks = floor($seconds / 604800);
    $seconds %= 604800;

    $days = floor($seconds / 86400);
    $seconds %= 86400;

    $hours = floor($seconds / 3600);
    $seconds %= 3600;

    $minutes = floor($seconds / 60);
    $seconds %= 60;

    $formattedTime = "";
    if ($weeks > 0) {
        $formattedTime .= $weeks . "w ";
    }
    if ($days > 0) {
        $formattedTime .= $days . "d ";
    }
    if ($hours > 0) {
        $formattedTime .= $hours . "h ";
    }
    if ($minutes > 0) {
        $formattedTime .= $minutes . "min ";
    }
    if ($seconds > 0) {
        $formattedTime .= $seconds . "sec";
    }

    return trim($formattedTime);
}


}

