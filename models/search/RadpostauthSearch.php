<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\radius\Radpostauth;

/**
 * RadpostauthSearch represents the model behind the search form of `\app\models\radius\Radpostauth`.
 */
class RadpostauthSearch extends Radpostauth
{

    public $fullname;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['username', 'pass', 'reply', 'authdate','fullname'], 'safe'],
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
        $query = Radpostauth::find()
        ->select(['radpostauth.id','username','pass','reply','authdate','users_inet.user_id as user_id','users.fullname as fullname','users_inet.u_s_p_i as u_s_p_i','service_packets.packet_name as packet_name','service_packets.packet_price as tariff','users_inet.status as inet_status'])
        ->leftjoin('users_inet','users_inet.login=radpostauth.username')
        ->leftjoin('users','users.id=users_inet.user_id')
        ->leftjoin('service_packets','service_packets.id=users_inet.packet_id')->asArray();

        // add conditions that should always apply here

        $cookieName = '_grid_page_size_radpostauth';
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
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'pass', $this->pass])
            ->andFilterWhere(['like', 'authdate', $this->authdate])
            ->andFilterWhere(['like', 'users.fullname', $this->fullname])
            ->andFilterWhere(['like', 'reply', $this->reply]);

        return $dataProvider;
    }
}
