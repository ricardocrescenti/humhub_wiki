<?php

namespace humhub\modules\wiki\models\forms;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentTag;
use humhub\modules\content\models\ContentTagRelation;
use humhub\modules\wiki\models\WikiPage;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ChildPagesGridFilterForm extends Model
{
    public int $parentPageId;
    public ContentContainerActiveRecord $contentContainer;

    /** Texto livre: título OU conteúdo da última revisão */
    public string $keyword = '';

    /** IDs de tópicos (multi) */
    public array $topic = [];

    public int $pageSize = 24;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['keyword'], 'safe'],
            [['topic'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'keyword' => Yii::t('WikiModule.base', 'Search'),
            'topic' => Yii::t('WikiModule.base', 'Topics'),
        ];
    }

    public function search(): ActiveDataProvider
    {
        $query = WikiPage::find()->alias('p')
            ->contentContainer($this->contentContainer)
            ->readable()
            ->andWhere(['p.parent_page_id' => $this->parentPageId]);

        /**
         * IMPORTANTÍSSIMO:
         * - Não dê alias no latestRevision aqui, para não esbarrar no problema do
         *   getLatestRevision() usar coluna qualificada (wiki_page_revision.is_latest).
         * - Assim, o Yii mantém o alias padrão "wiki_page_revision" e o LIKE funciona.
         */
        $query->joinWith('latestRevision', false);

        // Regra do grid: sempre por data de criação (desc)
        // "content" já existe no query por causa de contentContainer()
        $query->orderBy(['content.created_at' => SORT_DESC]);

        // Pesquisa tokenizada: todos os tokens devem bater em (título OU conteúdo)
        $keyword = trim($this->keyword);
        if ($keyword !== '') {
            $tokens = preg_split('/\s+/', $keyword, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach ($tokens as $token) {
                $query->andWhere([
                    'or',
                    ['like', 'p.title', $token],
                    ['like', 'wiki_page_revision.content', $token],
                ]);
            }
        }

        // Tópicos (qualquer um dos selecionados)
        if (!empty($this->topic)) {
            $query->innerJoin(['ctr' => ContentTagRelation::tableName()], 'ctr.content_id = content.id');
            $query->innerJoin(['ct' => ContentTag::tableName()], 'ct.id = ctr.tag_id');
            $query->andWhere(['ct.module_id' => 'topic']);
            $query->andWhere(['ctr.tag_id' => $this->topic]);
            $query->distinct();
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->pageSize,
                'pageParam' => 'childGridPage',
            ],
        ]);
    }
}
