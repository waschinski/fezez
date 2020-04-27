<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Offer;

/**
 * Offer2Search represents the model behind the search form of `app\models\Offer2`.
 */
class MarketplaceSearch extends OfferSearch
{
    
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Offer::find()
        ->where(['status' => Offer::STATUS_ACTIVE])
        ->andWhere(['not', ['user_id' => \Yii::$app->user->identity->ID]])
        ->orderBy(['created_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
