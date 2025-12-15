<?php

use humhub\libs\Html;
use humhub\modules\topic\widgets\TopicLabel;
use humhub\modules\wiki\models\WikiPage;
use humhub\modules\wiki\widgets\WikiSearchForm;
use yii\data\ActiveDataProvider;

/** @var $page WikiPage */
/** @var $dataProvider ActiveDataProvider */
/** @var $resetUrl string */
/** @var $keywordParam string */
/** @var $topicParam string */
/** @var $keyword string */
/** @var $topic array */
/** @var $topicsByContentId array<int, \humhub\modules\topic\models\Topic[]> */

$pages = $dataProvider->getModels();
?>

<div>
  <div class="row wiki-page-content-header">
    <?= WikiSearchForm::widget(['contentContainer' => $page->content->container, 'cssClass' => 'pull-left']) ?>
  </div>

  <div id="wiki-child-grid-<?= (int)$page->id ?>">
  
      <?php if (empty($pages)) : ?>
          <div class="help-block">
              <?= Yii::t('WikiModule.base', 'No subpages found.') ?>
          </div>
      <?php else: ?>
  
      <div class="table-responsive">
          <table class="table table-hover">
              <tbody>
              <?php foreach ($pages as $childPage): ?>
                  <tr>
                      <td>
                          <?= $this->render('wikiListTableRow', ['wikiPage' => $childPage]) ?>
                          <?php
                              $cid = $childPage->content ? (int)$childPage->content->id : 0;
                              $topics = $cid ? ($topicsByContentId[$cid] ?? []) : [];
                          ?>
                          <?php foreach ($topics as $topic) : ?>
                              <?php if ($topic instanceof \humhub\modules\topic\models\Topic) : ?>
                                  <span class="label label-default"><?= Html::encode($topic->name) ?></span>
                              <?php endif; ?>
                          <?php endforeach; ?>
                      </td>
                  </tr>
              <?php endforeach; ?>
              </tbody>
          </table>
      </div>
  
      <?= \humhub\widgets\LinkPager::widget([
          'pagination' => $dataProvider->pagination,
      ]) ?>
  
      <?php endif; ?>
  </div>
</div>