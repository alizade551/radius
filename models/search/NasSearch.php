<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\radius\Nas;

/**
 * RoutersSearch represents the model behind the search form of `app\models\Routers`.
 */
class NasSearch extends Nas
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['nasname', 'vendor_name', 'shortname', 'server'], 'safe'],
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
   
        $query = Nas::find();

        // add conditions that should always apply here
        $cookieName = '_grid_page_size_bras';

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

        $query->andFilterWhere(['like', 'nasname', $this->nasname])
            ->andFilterWhere(['like', 'vendor_name', $this->vendor_name])
            ->andFilterWhere(['like', 'shortname', $this->shortname])
            ->andFilterWhere(['like', 'server', $this->server]);

        return $dataProvider;
    }
}
