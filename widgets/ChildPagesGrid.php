<?php

namespace humhub\modules\wiki\widgets;

use humhub\components\Widget;
use humhub\modules\content\models\ContentTagRelation;
use humhub\modules\topic\models\Topic;
use humhub\modules\wiki\helpers\Url;
use humhub\modules\wiki\models\WikiPage;
use humhub\modules\wiki\models\forms\ChildPagesGridFilterForm;
use Yii;
use yii\db\Query;

class ChildPagesGrid extends Widget
{
    public WikiPage $page;
    public int $pageSize = 24;

    public function run()
    {
        $filter = new ChildPagesGridFilterForm([
            'parentPageId' => (int)$this->page->id,
            'contentContainer' => $this->page->content->container,
            'pageSize' => $this->pageSize,
        ]);

        $dataProvider = $filter->search();
        $pages = $dataProvider->getModels();

        return $this->render('childPagesGrid', [
            'page' => $this->page,
            'dataProvider' => $dataProvider,
            'resetUrl' => Url::toWiki($this->page),
            'topicsByContentId' => $this->loadTopicsByContentId($pages)
        ]);
    }

    /**
     * @param WikiPage[] $pages
     * @return array<int, Topic[]> indexado por content_id
     */
    protected function loadTopicsByContentId(array $pages): array
    {
        $contentIds = [];
        foreach ($pages as $p) {
            if ($p instanceof WikiPage && $p->content) {
                $contentIds[] = (int)$p->content->id;
            }
        }

        $contentIds = array_values(array_unique(array_filter($contentIds)));
        if (empty($contentIds)) {
            return [];
        }

        $rows = (new Query())
            ->select(['ctr.content_id', 't.id', 't.name', 't.color'])
            ->from(['ctr' => ContentTagRelation::tableName()])
            ->innerJoin(['t' => Topic::tableName()], 't.id = ctr.tag_id')
            ->where(['ctr.content_id' => $contentIds])
            ->andWhere(['t.module_id' => 'topic'])
            ->orderBy(['t.sort_order' => SORT_ASC, 't.name' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($rows as $row) {
            $topic = new Topic();
            $topic->setAttributes([
                'id' => (int)$row['id'],
                'name' => (string)$row['name'],
                'color' => (string)$row['color'],
            ], false);

            $cid = (int)$row['content_id'];
            $result[$cid] ??= [];
            $result[$cid][] = $topic;
        }

        return $result;
    }
}
