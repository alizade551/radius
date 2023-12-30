<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\radius\Radacct;
use yii\db\Query;

/**
 * RadacctAllSearch represents the model behind the search form of `\app\models\radius\Radacct`.
 */
class RadacctAllSearch extends Radacct
{

    public $login;
    public $fullname;
    public $tariff;
    public $packet_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['radacctid', 'acctinterval', 'acctsessiontime', 'acctinputoctets', 'acctoutputoctets'], 'integer'],
            [['acctsessionid', 'acctuniqueid', 'username', 'realm', 'nasipaddress', 'nasportid', 'nasporttype', 'acctstarttime', 'acctupdatetime', 'acctstoptime', 'acctauthentic', 'connectinfo_start', 'connectinfo_stop', 'calledstationid', 'callingstationid', 'acctterminatecause', 'servicetype', 'framedprotocol', 'framedipaddress', 'framedipv6address', 'framedipv6prefix', 'framedinterfaceid', 'delegatedipv6prefix','fullname','tariff','packet_name','login'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {


        $query = (new Query())
        ->select(['r.*','r.username','acctstarttime','acctupdatetime','acctstoptime','framedipaddress','callingstationid','nasipaddress','framedprotocol','nasporttype','servicetype','acctterminatecause','acctsessiontime','users_inet.user_id as user_id','users.fullname as fullname','users_inet.u_s_p_i as u_s_p_i','service_packets.packet_name as packet_name','service_packets.packet_price as tariff','users_inet.status as inet_status','ROUND(acctinputoctets / (1024 * 1024), 2) AS acctinputoctets','ROUND(acctoutputoctets / (1024 * 1024), 2) AS acctoutputoctets'])
        ->from(['r' => 'radacct'])
        ->leftjoin('users_inet','users_inet.login=r.username')
        ->leftjoin('users','users.id=users_inet.user_id')
        ->leftjoin('service_packets','service_packets.id=users_inet.packet_id')
        ->orderBy(['radacctid'=>SORT_DESC]);

        // add conditions that should always apply here

       $cookieName = '_grid_page_size_radacct_all';
       $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => \Yii::$app->request->cookies->getValue( $cookieName, 20),

            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'radacctid' => $this->radacctid,
            'acctstarttime' => $this->acctstarttime,
            'acctupdatetime' => $this->acctupdatetime,
            'acctstoptime' => $this->acctstoptime,
            'acctinterval' => $this->acctinterval,
            'acctsessiontime' => $this->acctsessiontime,
            'acctinputoctets' => $this->acctinputoctets,
            'acctoutputoctets' => $this->acctoutputoctets,
        ]);

        $query->andFilterWhere(['like', 'acctsessionid', $this->acctsessionid])
            ->andFilterWhere(['like', 'service_packets.packet_name', $this->packet_name])
            ->andFilterWhere(['like', 'service_packets.packet_price', $this->tariff])
            ->andFilterWhere(['like', 'users.fullname', $this->fullname])
            ->andFilterWhere(['like', 'acctuniqueid', $this->acctuniqueid])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'realm', $this->realm])
            ->andFilterWhere(['like', 'nasipaddress', $this->nasipaddress])
            ->andFilterWhere(['like', 'nasportid', $this->nasportid])
            ->andFilterWhere(['like', 'nasporttype', $this->nasporttype])
            ->andFilterWhere(['like', 'acctauthentic', $this->acctauthentic])
            ->andFilterWhere(['like', 'connectinfo_start', $this->connectinfo_start])
            ->andFilterWhere(['like', 'connectinfo_stop', $this->connectinfo_stop])
            ->andFilterWhere(['like', 'calledstationid', $this->calledstationid])
            ->andFilterWhere(['like', 'callingstationid', $this->callingstationid])
            ->andFilterWhere(['like', 'acctterminatecause', $this->acctterminatecause])
            ->andFilterWhere(['like', 'servicetype', $this->servicetype])
            ->andFilterWhere(['like', 'framedprotocol', $this->framedprotocol])
            ->andFilterWhere(['like', 'framedipaddress', $this->framedipaddress])
            ->andFilterWhere(['like', 'framedipv6address', $this->framedipv6address])
            ->andFilterWhere(['like', 'framedipv6prefix', $this->framedipv6prefix])
            ->andFilterWhere(['like', 'framedinterfaceid', $this->framedinterfaceid])
            ->andFilterWhere(['like', 'delegatedipv6prefix', $this->delegatedipv6prefix]);

        return $dataProvider;
    }
}
