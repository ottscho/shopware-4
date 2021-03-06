<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage Article
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */

/**
 * Shopware Backend Controller
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Backend_Article extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Repository for the article model.
     * @var \Shopware\Models\Article\Repository
     */
    protected $repository = null;
    /**
     * Repository for the shop model
     * @var \Shopware\Models\Shop\Repository
     */
    protected $shopRepository = null;
    /**
     * Repository for the customer model
     * @var \Shopware\Models\Customer\Repository
     */
    protected $customerRepository = null;
    /**
     * Repository for the category model
     * @var \Shopware\Models\Category\Repository
     */
    protected $categoryRepository = null;
    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $articleDetailRepository = null;
    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $customerGroupRepository = null;
    /**
     * Entity Manager
     * @var null
     */
    protected $manager = null;

    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $configuratorDependencyRepository = null;

    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $configuratorPriceSurchargeRepository = null;

    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $configuratorGroupRepository = null;

    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $configuratorOptionRepository = null;

    /**
     * @var Shopware_Components_Translation
     */
    protected $translation = null;

    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $configuratorSetRepository = null;

	public function initAcl()
    {
		$this->addAclPermission("loadStores","read","Insufficient Permissions");
		$this->addAclPermission("duplicateArticle","save","Insufficient Permissions");
		$this->addAclPermission("save","save","Insufficient Permissions");
		$this->addAclPermission("delete","delete","Insufficient Permissions");
	}
    /**
     * Disable template engine for all actions
     */
    public function preDispatch()
    {
        if(!in_array($this->Request()->getActionName(), array('index', 'load', 'validateNumber'))) {
            $this->Front()->Plugins()->Json()->setRenderer();
        } else {
            //$this->View()->setCaching();
        }

        //if the shopware cache was cleared, the browser cache will be cleared too.
        //if((($noCache = $this->Request()->getParam('no-cache'))
        //  && $this->View()->Template()->cached->timestamp < $noCache)
        //  || (($cacheControl = $this->Request()->getHeader('Cache-Control')) !== null
        //  && strpos($cacheControl, 'no-cache') !== false)) {
        //    $this->View()->Template()->cached->timestamp = $noCache;
        //    $this->View()->Template()->cached->valid = false;
        //}
    }

    /**
     * @return Shopware_Components_Translation
     */
    protected function getTranslationComponent()
    {
        if ($this->translation === null) {
            $this->translation = new Shopware_Components_Translation();
        }

        return $this->translation;
    }

    /**
     * Internal helper function to get access to the entity manager.
     * @return Shopware\Components\Model\ModelManager
     */
    protected function getManager() {
        if ($this->manager === null) {
            $this->manager= Shopware()->Models();
        }
        return $this->manager;
    }

    /**
     * Internal helper function to get access to the article repository.
     *
     * @return Shopware\Models\Article\Repository
     */
    protected function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
        }
        return $this->repository;
    }

    /**
     * Helper function to get access to the customerGroup repository.
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getCustomerGroupRepository() {
    	if ($this->customerGroupRepository === null) {
    		$this->customerGroupRepository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Group');
    	}
    	return $this->customerGroupRepository;
    }

    /**
     * Helper function to get access to the articleDetail repository.
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getArticleDetailRepository() {
    	if ($this->articleDetailRepository === null) {
    		$this->articleDetailRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');
    	}
    	return $this->articleDetailRepository;
    }

    /**
     * Internal helper function to get access to the article repository.
     *
     * @return Shopware\Models\Article\Repository
     */
    protected function getCustomerRepository()
    {
        if ($this->customerRepository === null) {
            $this->customerRepository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer');
        }
        return $this->customerRepository;
    }

    /**
     * Internal helper function to get access on the shop repository.
     * @return null|Shopware\Models\Shop\Repository
     */
    protected function getShopRepository() {
        if ($this->shopRepository === null) {
            $this->shopRepository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        }

        return $this->shopRepository;
    }

    /**
     * Internal helper function to get access on the category repository.
     * @return null|Shopware\Models\Category\Repository
     */
    protected function getCategoryRepository() {
        if ($this->categoryRepository === null) {
            $this->categoryRepository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
        }
        return $this->categoryRepository;
    }

    /**
     * Helper function to get access to the configuratorPriceSurcharge repository.
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getConfiguratorPriceSurchargeRepository() {
    	if ($this->configuratorPriceSurchargeRepository === null) {
    		$this->configuratorPriceSurchargeRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Configurator\PriceSurcharge');
    	}
    	return $this->configuratorPriceSurchargeRepository;
    }

    /**
     * Helper function to get access to the ConfiguratorDependency repository.
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getConfiguratorDependencyRepository() {
    	if ($this->configuratorDependencyRepository === null) {
    		$this->configuratorDependencyRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Configurator\Dependency');
    	}
    	return $this->configuratorDependencyRepository;
    }

    /**
     * Helper function to get access to the configuratorGroup repository.
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getConfiguratorGroupRepository() {
    	if ($this->configuratorGroupRepository === null) {
    		$this->configuratorGroupRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Configurator\Group');
    	}
    	return $this->configuratorGroupRepository;
    }
    /**
     * Helper function to get access to the configuratorOption repository.
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getConfiguratorOptionRepository() {
    	if ($this->configuratorOptionRepository === null) {
    		$this->configuratorOptionRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Configurator\Option');
    	}
    	return $this->configuratorOptionRepository;
    }

    /**
     * Helper function to get access to the configuratorSet repository.
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getConfiguratorSetRepository() {
    	if ($this->configuratorSetRepository === null) {
    		$this->configuratorSetRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Configurator\Set');
    	}
    	return $this->configuratorSetRepository;
    }

    /**
     * Event listener function of the article backend module. Fired when the user
     * edit or create an article and clicks the save button which displayed on bottom of the article
     * detail window.
     */
    public function saveAction()
    {
        $data = $this->Request()->getParams();
        if ($this->Request()->has('id')) {
            $article = $this->getRepository()->find((int) $this->Request()->getParam('id'));
        } else {
            $article = new \Shopware\Models\Article\Article();
        }
        $this->saveArticle($data, $article);
    }

    /**
     * Event listener function of the configurator set model in the article backend module.
     */
    public function saveConfiguratorSetAction()
    {
        try {
            $data = $this->Request()->getParams();
            $id = (int) $data['id'];
            $articleId = (int) $data['articleId'];

            if (!empty($articleId)) {
                $article = Shopware()->Models()->find('Shopware\Models\Article\Article', $articleId);
                if ($article->getConfiguratorSet()->getId() !== $id) {
                    Shopware()->Models()->remove($article->getConfiguratorSet());
                    Shopware()->Models()->flush();
                }
            }

            if (!empty($id) && $id > 0) {
                $configuratorSet = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Set', $id);
            } else {
                $configuratorSet = new \Shopware\Models\Article\Configurator\Set();
            }
            if (!$configuratorSet) {
                $this->View()->assign(array(
                    'success' => false,
                    'noId' => true
                ));
                return;
            }

            $groups = array();
            foreach($data['groups'] as $groupData) {
                if (!empty($groupData['id']) && $groupData['active']) {
                    $group = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Group', $groupData['id']);
                    $group->setPosition($groupData['position']);
                    $groups[] = $group;
                }
            }
            $data['groups'] = $groups;

            $options = array();
            foreach($data['options'] as $optionData) {
                if (!empty($optionData['id']) && $optionData['active']) {
                    $option = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Option', $optionData['id']);
                    $option->setPosition($optionData['position']);
                    $options[] = $option;
                }
            }
            $data['options'] = $options;
            if ($configuratorSet->getOptions()) {
                $configuratorSet->getOptions()->clear();
            }
            if ($configuratorSet->getGroups()) {
                $configuratorSet->getGroups()->clear();
            }
            $configuratorSet->fromArray($data);
            Shopware()->Models()->persist($configuratorSet);
            Shopware()->Models()->flush();

            if (!empty($articleId)) {
                /**@var $article \Shopware\Models\Article\Article*/
                $article = Shopware()->Models()->find('Shopware\Models\Article\Article', $articleId);
                $article->setConfiguratorSet($configuratorSet);
                Shopware()->Models()->persist($article);
                Shopware()->Models()->flush();
            }

            $data = $this->getRepository()->getConfiguratorSetQuery($configuratorSet->getId())->getArrayResult();
            $this->View()->assign(array(
                'success' => true,
                'data' => $data
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Event listener function of the backend article module. Fired when the user want to accept the
     * variant data of the main detail to the selected variant(s).
     */
    public function acceptMainDataAction() {
        try {
            $data = $this->Request()->getParams();
            $articleId = (int) $data['articleId'];
            if (empty($articleId)) {
                $this->View()->assign(array('success' => false,'noId' => true));
                return;
            }

            /**@var $article \Shopware\Models\Article\Article*/
            $article = Shopware()->Models()->find('Shopware\Models\Article\Article', $articleId);
            $mainDetail = $article->getMainDetail();
            $mainData = $this->getMappingData($mainDetail, $data);
            $variants = $this->getVariantsForMapping($articleId, $mainDetail, $data);
            if (!empty($variants)) {
                /**@var $variant \Shopware\Models\Article\Detail*/
                foreach($variants as $variant) {
                    $variant->fromArray($mainData);
                    Shopware()->Models()->persist($variant);
                }
                Shopware()->Models()->flush();
            }
            $this->View()->assign(array('success' => true));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Internal helper function to which returns all article variants which are not the main detail
     * or the backend variant.
     * @param $articleId
     * @param $mainDetail
     * @param $mapping
     * @return array
     */
    protected function getVariantsForMapping($articleId, $mainDetail, $mapping)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('details'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->where('details.id != ?1')
                ->andWhere('details.articleId = ?2')
                ->setParameter(1, $mainDetail->getId())
                ->setParameter(2, $articleId);

        if (!empty($mapping['variants'])) {
            $ids = array();
            foreach($mapping['variants'] as $variant) {
                $ids[] = $variant['id'];
            }
            if (!empty($ids)) {
                $builder->andWhere('details.id IN (?3)')
                        ->setParameter(3, $ids);
            }
        }
        $variants =$builder->getQuery()->getResult();
        return $variants;
    }

    /**
     * Returns the main detail data for the variant mapping action.
     * @param $mainDetail \Shopware\Models\Article\Detail
     * @param $mapping
     * @return array
     */
    protected function getMappingData($mainDetail, $mapping)
    {
        $mainData = array();
        if ($mapping['settings']) {
            $mainData['supplierNumber'] = $mainDetail->getSupplierNumber();
            $mainData['weight'] = $mainDetail->getWeight();
            $mainData['inStock'] = $mainDetail->getInStock();
            $mainData['stockMin'] = $mainDetail->getStockMin();
            $mainData['ean'] = $mainDetail->getEan();
            $mainData['minPurchase'] = $mainDetail->getMinPurchase();
            $mainData['purchaseSteps'] = $mainDetail->getPurchaseSteps();
            $mainData['maxPurchase'] = $mainDetail->getMaxPurchase();
            $mainData['releaseDate'] = $mainDetail->getReleaseDate();
            $mainData['shippingTime'] = $mainDetail->getShippingTime();
            $mainData['shippingFree'] = $mainDetail->getShippingFree();
        }
        if ($mapping['attributes']) {
            $builder = Shopware()->Models()->createQueryBuilder();
            $mainData['attribute'] = $builder->select(array('attributes'))
                    ->from('Shopware\Models\Attribute\Article', 'attributes')
                    ->where('attributes.articleId = ?1')
                    ->andHaving('attributes.articleDetailId = :detailId')
                    ->setParameter(1, $mainDetail->getArticle()->getId())
                    ->setParameter('detailId', $mainDetail->getId())
                    ->setFirstResult(0)
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            unset($mainData['attribute']['id']);
            unset($mainData['attribute']['articleId']);
            unset($mainData['attribute']['articleDetailId']);
            unset($mainData['attribute']['articleDetail']);
            $mainData['attribute']['article'] = $mainDetail->getArticle();
        }
        if ($mapping['prices']) {
            $builder = Shopware()->Models()->createQueryBuilder();
            $prices = $builder->select(array('prices', 'customerGroup'))
                              ->from('Shopware\Models\Article\Price', 'prices')
                              ->innerJoin('prices.customerGroup', 'customerGroup')
                              ->where('prices.articleDetailsId = ?1')
                              ->setParameter(1, $mainDetail->getId())
                              ->getQuery()
                              ->getArrayResult();

            foreach($prices as $key => $price) {
                unset($price['id']);
                $price['customerGroup'] = $this->getCustomerGroupRepository()->find($price['customerGroup']['id']);
                $price['article'] = $mainDetail->getArticle();
                $prices[$key] = $price;
            }
            $mainData['prices'] = $prices;
        }
        if ($mapping['basePrice']) {
            $mainData['unit'] = $mainDetail->getUnit();
            $mainData['purchaseUnit'] = $mainDetail->getPurchaseUnit();
            $mainData['referenceUnit'] = $mainDetail->getReferenceUnit();
            $mainData['packUnit'] = $mainDetail->getPackUnit();
        }
        return $mainData;
    }

    /**
     * Event listener function of the article backend module. Fired when the user clicks the "duplicate article" button
     * on the detail page to duplicate the whole article configuration for a new article.
     */
    public function duplicateArticleAction() {
        try {
            $articleId = $this->Request()->getParam('articleId', null);

            if (empty($articleId)) {
                $this->View()->assign(array(
                    'success' => false,
                    'noId' => true
                ));
            }

            /**
             * @var $article Shopware\Models\Article\Article
             */
            $article = Shopware()->Models()->find('Shopware\Models\Article\Article', $articleId);
            if ($article->getConfiguratorSet() !== null) {
                $isConfigurator = true;
                $mailDetailId   = $article->getMainDetail()->getId();
            } else {
                $isConfigurator = false;
                $mailDetailId   = null;
            }

            $this->duplicateArticleData($articleId);
            $newArticleId = Shopware()->Db()->lastInsertId('s_articles');
            $this->duplicateArticleCategories($articleId, $newArticleId);
            $this->duplicateArticleCustomerGroups($articleId, $newArticleId);
            $this->duplicateArticleRelated($articleId, $newArticleId);
            $this->duplicateArticleSimilar($articleId, $newArticleId);
            $this->duplicateArticleDetails($articleId, $newArticleId, $mailDetailId);
            $this->duplicateArticleLinks($articleId, $newArticleId);
            $this->duplicateArticleImages($articleId, $newArticleId);
            $this->duplicateArticleProperties($articleId, $newArticleId);
            $this->duplicateArticleDownloads($articleId, $newArticleId);
            $setId = $this->duplicateArticleConfigurator($articleId, $newArticleId);

            $sql= "UPDATE s_articles, s_articles_details SET main_detail_id = s_articles_details.id
                    WHERE s_articles_details.articleID = s_articles.id
                    AND s_articles.id = ?
                    AND s_articles_details.kind = 1";
            Shopware()->Db()->query($sql, array($newArticleId));

            if ($setId !== null) {
                $sql= "UPDATE s_articles SET configurator_set_id = ?
                        WHERE s_articles.id = ?";
                Shopware()->Db()->query($sql, array($setId, $newArticleId));
            }


            $this->View()->assign(array(
                'success' => true,
                'articleId' => $newArticleId,
                'isConfigurator' => $isConfigurator
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Internal helper function which duplicates the article data of the s_articles.
     * @param $articleId
     */
    protected function duplicateArticleData($articleId)
    {
        $sql= "INSERT INTO s_articles
               SELECT NULL,
                   supplierID, CONCAT(name, '-', 'Copy'), description, description_long, shippingtime, datum, active, taxID, pseudosales, topseller, keywords, changetime, pricegroupID, pricegroupActive, filtergroupID, laststock, crossbundlelook, notification, template, mode, NULL, available_from, available_to, NULL
               FROM s_articles as source
               WHERE source.id = ?";
        Shopware()->Db()->query($sql, array($articleId));
    }

    /**
     * Internal helper function which duplicates the assigned categories of the article to the new article.
     * @param $articleId
     * @param $newArticleId
     */
    protected function duplicateArticleCategories($articleId, $newArticleId)
    {
        $sql= "INSERT INTO s_articles_categories
               SELECT NULL, ?, categoryID
               FROM s_articles_categories as source
               WHERE source.articleID = ?
        ";
        Shopware()->Db()->query($sql, array($newArticleId, $articleId));
    }

    /**
     * Internal helper function to duplicate the avoid customer group configuration from the passed article
     * id to the new article.
     * @param $articleId
     * @param $newArticleId
     */
    protected function duplicateArticleCustomerGroups($articleId, $newArticleId)
    {
        $sql= "INSERT INTO s_articles_avoid_customergroups
               SELECT ?, customergroupID
               FROM s_articles_avoid_customergroups as source
               WHERE source.articleID = ?
        ";
        Shopware()->Db()->query($sql, array($newArticleId, $articleId));
    }

    /**
     * Internal helper function to duplicate the related article configuration from the passed article
     * to the new article.
     * @param $articleId
     * @param $newArticleId
     */
    protected function duplicateArticleRelated($articleId, $newArticleId)
    {
        $sql= "INSERT INTO s_articles_relationships
               SELECT NULL, ?, relatedarticle
               FROM s_articles_relationships as source
               WHERE source.articleID = ?
        ";
        Shopware()->Db()->query($sql, array($newArticleId, $articleId));
    }

    /**
     * Internal helper function to duplicate the similar article configuration from the passed article
     * to the new article.
     * @param $articleId
     * @param $newArticleId
     */
    protected function duplicateArticleSimilar($articleId, $newArticleId)
    {
        $sql= "INSERT INTO s_articles_similar
               SELECT NULL, ?, relatedarticle
               FROM s_articles_similar as source
               WHERE source.articleID = ?
        ";
        Shopware()->Db()->query($sql, array($newArticleId, $articleId));
    }

    /**
     * Internal helper function to duplicate the article link configuration from the passed article
     * to the new article.
     * @param $articleId
     * @param $newArticleId
     */
    protected function duplicateArticleLinks($articleId, $newArticleId)
    {
        $article = Shopware()->Models()->find('Shopware\Models\Article\Article', $newArticleId);

        $builder = Shopware()->Models()->createQueryBuilder();
        $links = $builder->select(array('links', 'attribute'))
                ->from('Shopware\Models\Article\Link', 'links')
                ->leftJoin('links.attribute', 'attribute')
                ->where('links.articleId = ?1')
                ->setParameter(1, $articleId)
                ->getQuery()
                ->getArrayResult();

        foreach($links as $data) {
            $link = new \Shopware\Models\Article\Link();
            $link->fromArray($data);
            $link->setArticle($article);
            Shopware()->Models()->persist($link);
        }
        Shopware()->Models()->flush();
    }

    /**
     * Internal helper function to duplicate the download configuration from the passed article
     * to the new article.
     * @param $articleId
     * @param $newArticleId
     */
    protected function duplicateArticleDownloads($articleId, $newArticleId)
    {
        $article = Shopware()->Models()->find('Shopware\Models\Article\Article', $newArticleId);

        $builder = Shopware()->Models()->createQueryBuilder();
        $downloads = $builder->select(array('downloads', 'attribute'))
                ->from('Shopware\Models\Article\Download', 'downloads')
                ->leftJoin('downloads.attribute', 'attribute')
                ->where('downloads.articleId = ?1')
                ->setParameter(1, $articleId)
                ->getQuery()
                ->getArrayResult();

        foreach($downloads as $data) {
            $download = new \Shopware\Models\Article\Download();
            $download->fromArray($data);
            $download->setArticle($article);
            Shopware()->Models()->persist($download);
        }
        Shopware()->Models()->flush();
    }

    /**
     * Internal helper function to duplicate the image configuration from the passed article
     * to the new article.
     * @param $articleId
     * @param $newArticleId
     */
    protected function duplicateArticleImages($articleId, $newArticleId)
    {
        $article = Shopware()->Models()->find('Shopware\Models\Article\Article', $newArticleId);

        $builder = Shopware()->Models()->createQueryBuilder();
        $images = $builder->select(array('images', 'media', 'attribute', 'mappings', 'rules', 'option'))
                ->from('Shopware\Models\Article\Image', 'images')
                ->leftJoin('images.attribute', 'attribute')
                ->leftJoin('images.mappings', 'mappings')
                ->leftJoin('images.media', 'media')
                ->leftJoin('mappings.rules', 'rules')
                ->leftJoin('rules.option', 'option')
                ->where('images.articleId = ?1')
                ->andWhere('images.parentId IS NULL')
                ->setParameter(1, $articleId)
                ->getQuery()
                ->getArrayResult();


        foreach ($images as &$data) {
            if (!empty($data['mappings'])) {
                foreach($data['mappings'] as $mappingKey => $mapping) {
                    foreach ($mapping['rules'] as $ruleKey => $rule) {
                        $option = Shopware()->Models()->find('\Shopware\Models\Article\Configurator\Option', $rule['optionId']);
                        if ($option) {
                            $rule['option'] = $option;
                            $data['mappings'][$mappingKey]['rules'][$ruleKey]['option'] = $option;
                        }
                    }
                }
            }

            if (!empty($data['mediaId'])) {
                $data['media'] = Shopware()->Models()->find('\Shopware\Models\Media\Media', $data['mediaId']);
                if (!$data['media']) {
                     unset($data['media']);
                }
            }

            $image = new \Shopware\Models\Article\Image();
            $image->fromArray($data);
            $image->setArticle($article);
            $image->setArticleDetail(null);

            Shopware()->Models()->persist($image);
        }

        Shopware()->Models()->flush();
    }

    /**
     * Internal helper function to duplicate the property configuration from the passed article
     * to the new article.
     * @param $articleId
     * @param $newArticleId
     */
    protected function duplicateArticleProperties($articleId, $newArticleId)
    {
        $sql= "INSERT INTO s_filter_articles
               SELECT ?, valueID
               FROM s_filter_articles as source
               WHERE source.articleID = ?
        ";
        Shopware()->Db()->query($sql, array($newArticleId, $articleId));
    }

    /**
     * Internal helper function to duplicate the variant configuration from the passed article
     * to the new article.
     * @param $articleId
     * @param $newArticleId
     */
    protected function duplicateArticleDetails($articleId, $newArticleId, $mailDetailId = null)
    {
        $article = Shopware()->Models()->find('Shopware\Models\Article\Article', $newArticleId);
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('details', 'prices', 'attribute', 'images'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->leftJoin('details.prices', 'prices')
                ->leftJoin('details.attribute', 'attribute')
                ->leftJoin('details.images', 'images')
                ->where('details.articleId = ?1');

        if ($mailDetailId !== null){
            $builder->andWhere('details.id = ?2');
            $details = $builder->setParameter(1, $articleId)
                               ->setParameter(2, $mailDetailId)
                               ->getQuery()
                               ->getArrayResult();
        } else {
            $details = $builder->setParameter(1, $articleId)
                               ->getQuery()
                               ->getArrayResult();
        }

        $newArticleData = $this->getNewArticleData();
        $number = $newArticleData['number'];

        foreach ($details as $data) {
            $prices = array();
            $data['number'] = $number;
            $detail = new \Shopware\Models\Article\Detail();

            foreach($data['prices'] as $priceData) {
                if (empty($priceData['customerGroupKey'])) {
                    continue;
                }
                $customerGroup = $this->getCustomerGroupRepository()->findOneBy(array('key' => $priceData['customerGroupKey']));
                if ($customerGroup instanceof \Shopware\Models\Customer\Group) {
                    $priceData['customerGroup'] = $customerGroup;
                    $priceData['article'] = $article;
                    $prices[] = $priceData;
                }
            }
            $data['prices'] = $prices;

            // unset configuratorOptions and images. These are variantspecific and are going to be recreated later
            unset($data['images']);
            unset($data['configuratorOptions']);

            if (!empty($data['unitId'])) {
                $data['unit'] = Shopware()->Models()->find('Shopware\Models\Article\Unit', $data['unitId']);
            } else {
                $data['unit'] = null;
            }

            if (!empty($data['attribute'])) {
                $data['attribute']['article'] = $article;
            }

            $data['article'] = $article;

            $detail->fromArray($data);
            Shopware()->Models()->persist($detail);
        }
        Shopware()->Models()->flush();

        $this->increaseAutoNumber($newArticleData['autoNumber'], $number);

    }

    /**
     * @param $articleId
     * @return null|string
     */
    protected function duplicateArticleConfigurator($articleId)
    {
        $unique = uniqid();

        /**@var $oldArticle \Shopware\Models\Article\Article*/
        $oldArticle = Shopware()->Models()->find('Shopware\Models\Article\Article', $articleId);
        if (!$oldArticle->getConfiguratorSet()) {
            return null;
        }

        $oldSetId = $oldArticle->getConfiguratorSet()->getId();

        $sql= "INSERT INTO s_article_configurator_sets
                SELECT NULL, CONCAT(name, '-', '". $unique ."'), public, type
                FROM s_article_configurator_sets as source
                WHERE source.id = ?";

        Shopware()->Db()->query($sql, array($oldSetId));
        $newSetId = Shopware()->Db()->lastInsertId('s_article_configurator_sets');

        $sql= "INSERT INTO s_article_configurator_set_group_relations
                SELECT ?, group_id
                FROM s_article_configurator_set_group_relations as source
                WHERE source.set_id = ?";
        Shopware()->Db()->query($sql, array($newSetId, $oldSetId));


        $sql= "INSERT INTO s_article_configurator_set_option_relations
                SELECT ?, option_id
                FROM s_article_configurator_set_option_relations as source
                WHERE source.set_id = ?";
        Shopware()->Db()->query($sql, array($newSetId, $oldSetId));

        $sql= "INSERT INTO s_article_configurator_dependencies
                SELECT NULL, ?, parent_id, child_id
                FROM s_article_configurator_dependencies as source
                WHERE source.configurator_set_id = ?";
        Shopware()->Db()->query($sql, array($newSetId, $oldSetId));

        $sql= "INSERT INTO s_article_configurator_price_surcharges
                SELECT NULL, ?, parent_id, child_id, surcharge
                FROM s_article_configurator_price_surcharges as source
                WHERE source.configurator_set_id = ?";
        Shopware()->Db()->query($sql, array($newSetId, $oldSetId));

        return $newSetId;
    }


    /**
     *
     */
    public function deleteAllVariantsAction()
    {
        try {
            $articleId = (int) $this->Request()->getParam('articleId');
            if (empty($articleId)) {
                $this->View()->assign(array(
                    'success' => false
                ));
                return;
            }
            $this->removeAllConfiguratorVariants($articleId);
            $this->View()->assign(array(
                'success' => true
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Internal helper function to remove all article variants.
     * @param $articleId
     */
    protected function removeAllConfiguratorVariants($articleId) {
        $builder = Shopware()->Models()->createQueryBuilder();
        $details = $builder->select(array('details', 'configuratorOptions'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->innerJoin('details.configuratorOptions', 'configuratorOptions')
                ->where('details.articleId = ?1')
                ->setParameter(1, $articleId)
                ->getQuery()
                ->getArrayResult();

        /**@var $article \Shopware\Models\Article\Article*/
        $article = $this->getRepository()->find($articleId);
        $mainDetailId = $article->getMainDetail()->getId();

        if (empty($details)) {
            return;
        }

        $detailIds = array();
        foreach($details as $detail) {
            if (empty($detail['configuratorOptions'])) {
                continue;
            }
            if($mainDetailId == $detail['id']) {
                continue;
            }
            $detailIds[] = $detail['id'];
        }

        if (count($detailIds) == 0) {
            return;
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Attribute\Article', 'details')
                ->andWhere('details.articleDetailId IN (?1)')
                ->setParameter(1, $detailIds)
                ->getQuery()
                ->execute();

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Article\Detail', 'details')
                ->andWhere('details.id IN (?1)')
                ->setParameter(1, $detailIds)
                ->getQuery()
                ->execute();

        $sql= "DELETE FROM s_article_configurator_option_relations WHERE article_id IN (?)";
        Shopware()->Db()->query($sql, array(implode(',', $detailIds)));

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Article\Price', 'prices')
                ->andWhere('prices.articleDetailsId IN (?1)')
                ->setParameter(1, $detailIds)
                ->getQuery()
                ->execute();
    }


    public function saveMediaMappingAction()
    {
        try {
            $imageId = (int) $this->Request()->getParam('id', null);
            $mappings = $this->Request()->getParam('mappings');

            if (empty($imageId) || $imageId <= 0) {
                $this->View()->assign(array('success' => false, 'noId' => true));
                return;
            }

            $query = $this->getRepository()->getArticleImageDataQuery($imageId);
            $image = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
            $imageData = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            $this->getRepository()->getDeleteImageChildrenQuery($imageId)->execute();

            $mappingModels = array();
            foreach($mappings as $mappingData) {
                if (empty($mappingData['rules'])) {
                    continue;
                }
                if (empty($mappingData['id'])) {
                    $mapping = new \Shopware\Models\Article\Image\Mapping();
                } else {
                    $mapping = Shopware()->Models()->find('Shopware\Models\Article\Image\Mapping', $mappingData['id']);
                }

                $mapping->getRules()->clear();
                $options = array();
                foreach($mappingData['rules'] as $ruleData) {
                    $rule = new \Shopware\Models\Article\Image\Rule();
                    $option = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Option', $ruleData['optionId']);
                    $rule->setMapping($mapping);
                    $rule->setOption($option);
                    $mapping->getRules()->add($rule);
                    $options[] = $option;
                }
                $mapping->setImage($image);
                Shopware()->Models()->persist($mapping);
                $this->createImagesForOptions($options, $imageData, $image);
                $mappingModels[] = $mapping;
            }
            $image->setMappings($mappingModels);
            Shopware()->Models()->persist($image);
            Shopware()->Models()->flush();

            $result = $this->getRepository()->getArticleImageQuery($imageId)->getArrayResult();

            $this->View()->assign(array('success' => true, 'data' => $result));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * @param $options
     * @param $imageData
     * @param $parent \Shopware\Models\Article\Image
     */
    protected function createImagesForOptions($options, $imageData, $parent)
    {
        $articleId = $parent->getArticle()->getId();
        $imageData['path'] = null;
        $imageData['parent'] = $parent;

        $details = $this->getRepository()->getDetailsForOptionIdsQuery($articleId, $options)->getResult();

        foreach($details as $detail) {
            $image = new \Shopware\Models\Article\Image();
            $image->fromArray($imageData);
            $image->setArticleDetail($detail);
            Shopware()->Models()->persist($image);
        }
        Shopware()->Models()->flush();
    }

    /**
     * Event listener function of the article backend module. Fired when the user
     * edit or create an article variant and clicks the save button which displayed on bottom of the article
     * variant detail window.
     */
    public function saveDetailAction()
    {
        try {
            $data = $this->Request()->getParams();
            $id = (int) $this->Request()->getParam('id');

            if ($id > 0) {
                $detail = $this->getArticleDetailRepository()->find($id);
            } else {
                $detail = new \Shopware\Models\Article\Detail();
            }
            $detail = $this->saveDetail($data, $detail);
            $data['id'] = $detail->getId();
            $this->View()->assign(array(
                'success' => true,
                'data' => $data
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * @param $data array
     * @param $detail \Shopware\Models\Article\Detail
     * @return \Shopware\Models\Article\Detail
     */
    protected function saveDetail($data, $detail)
    {
        $article = $detail->getArticle();
        $data['prices'] = $this->preparePricesAssociatedData($data['prices'], $article, $article->getTax());
        $data['attribute'] = $data['attribute'][0];
        $data['article'] = $article;
        unset($data['images']);
        if (!empty($data['unitId'])) {
            $data['unit'] = Shopware()->Models()->find('Shopware\Models\Article\Unit', $data['unitId']);
        } else {
            $data['unit'] = null;
        }

        unset($data['configuratorOptions']);
        $detail->fromArray($data);
        Shopware()->Models()->persist($detail);
        Shopware()->Models()->flush();
        if ($data['standard']) {
            $mainDetail = $article->getMainDetail();
            $mainDetail->setKind(2);
            $article->setMainDetail($detail);
            Shopware()->Models()->persist($mainDetail);
            Shopware()->Models()->persist($article);
            Shopware()->Models()->flush();

            // if main variant changed, swap translations
            if($mainDetail->getId() !== $detail->getId()) {
                $this->swapDetailTranslations($detail, $mainDetail);
            }

        }
        return $detail;
    }

    /**
     * Helper method which swaps the translations of the newMainDetail and the oldMainDetail
     * Needed because mainDetails' translations are stored for the article, not for the variant itself
     * @param \Shopware\Models\Article\Detail $newMainDetail
     * @param \Shopware\Models\Article\Detail $oldMainDetail
     */
    private function swapDetailTranslations($newMainDetail, $oldMainDetail)
    {
        $articleId = $oldMainDetail->getArticle()->getId();

        // Get available translations for the old mainDetail (stored on the article)
        $sql = "SELECT objectlanguage, objectdata FROM s_core_translations WHERE objecttype='article' AND objectkey=?";
        $oldTranslations = Shopware()->Db()->fetchAssoc($sql, array($articleId));

        // Get available translations for the new mainDetail (stored for the detail)
        $sql = "SELECT objectlanguage, objectdata FROM s_core_translations WHERE objecttype='variant' AND objectkey=?";
        $newTranslations = Shopware()->Db()->fetchAssoc($sql, array($newMainDetail->getId()));

        // We need to determine which of the old article translations can be used for the translation of the
        // variant which was the mainDetail before.
        // We'll get a list of translatable variant fields from the variant which is going to become the new mainDetail
        $translatedFields = array();
        foreach ($newTranslations as $values) {
            $data = $values['objectdata'];
            foreach (unserialize($data) as $field => $translation) {
                if (!array_key_exists($field, $translatedFields)) {
                    $translatedFields[$field] = true;
                }
            }
        }

        // Save the old article translation as new variant translations
        foreach ($oldTranslations as $language => $values) {
            $data = unserialize($values['objectdata']);
            $newData = array_intersect_key($data, $translatedFields);
            $this->getTranslationComponent()->write(
                $language,
                'variant',
                $oldMainDetail->getId(),
                $newData,
                false
            );

        }

        // Save the new mainDetail translations as article translations
        foreach ($newTranslations as $language => $values) {
            $data = unserialize($values['objectdata']);
            $newData = array_intersect_key($data, $translatedFields);
            $this->getTranslationComponent()->write(
                $language,
                'article',
                $articleId,
                $newData,
                false
            );
        }
    }

    /**
     * Event listener function of the article backend module. Fired when the user saves or updates
     * an article configurator dependency in the dependency window.
     */
    public function saveConfiguratorDependencyAction()
    {
        try {
            $data = $this->Request()->getParams();
            $id = (int) $this->Request()->getParam('id');
            if ($id > 0) {
                $dependency = $this->getConfiguratorDependencyRepository()->find($id);
            } else {
                $dependency = new \Shopware\Models\Article\Configurator\Dependency();
            }

            $data['childOption'] = $this->getConfiguratorOptionRepository()->find($data['childId']);
            $data['parentOption'] = $this->getConfiguratorOptionRepository()->find($data['parentId']);
            $data['configuratorSet'] = $this->getConfiguratorSetRepository()->find($data['configuratorSetId']);
            $dependency->fromArray($data);
            Shopware()->Models()->persist($dependency);
            Shopware()->Models()->flush();

            $builder = Shopware()->Models()->createQueryBuilder();
            $data = $builder->select(array('dependency', 'dependencyParent', 'dependencyChild'))
                    ->from('Shopware\Models\Article\Configurator\Dependency', 'dependency')
                    ->leftJoin('dependency.parentOption', 'dependencyParent')
                    ->leftJoin('dependency.childOption', 'dependencyChild')
                    ->where('dependency.id = ?1')
                    ->setParameter(1, $dependency->getId())
                    ->getQuery()
                    ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            $this->View()->assign(array(
                'success' => true,
                'data' => $data
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Event listener function of the article backend module.
     * Fired when the user want to load a configurator set in the configurator tab.
     * The function returns all public defined configurator sets without the passed ids.
     */
    public function getConfiguratorSetsAction()
    {
        $id = $this->Request()->getParam('setId');
        $sets = $this->getRepository()->getConfiguratorSetsWithExcludedIdsQuery($id)->getArrayResult();
        $this->View()->assign(array(
            'success' => true,
            'data' => $sets
        ));
    }

    /**
     * Event listener function of the article backend module. Fired when the user saves or updates
     * an article configurator price surcharge in the dependency window.
     */
    public function saveConfiguratorPriceSurchargeAction()
    {
        try {
            $data = $this->Request()->getParams();
            if (!empty($data['id']) && $data['id'] > 0) {
                $priceSurcharge = $this->getConfiguratorPriceSurchargeRepository()->find($data['id']);
            } else {
                $priceSurcharge = new \Shopware\Models\Article\Configurator\PriceSurcharge();
            }

            $data['parentOption'] = $this->getConfiguratorOptionRepository()->find($data['parentId']);

            if (!empty($data['childId']) && $data['childId'] > 0) {
                $data['childOption'] = $this->getConfiguratorOptionRepository()->find($data['childId']);
            } else {
                $data['childOption'] = null;
            }
            $data['configuratorSet'] = $this->getConfiguratorSetRepository()->find($data['configuratorSetId']);

            $priceSurcharge->fromArray($data);
            Shopware()->Models()->persist($priceSurcharge);
            Shopware()->Models()->flush();

            $builder = Shopware()->Models()->createQueryBuilder();
            $data = $builder->select(array('priceSurcharge', 'priceSurchargesParent', 'priceSurchargesChild'))
                    ->from('Shopware\Models\Article\Configurator\PriceSurcharge', 'priceSurcharge')
                    ->leftJoin('priceSurcharge.parentOption', 'priceSurchargesParent')
                    ->leftJoin('priceSurcharge.childOption', 'priceSurchargesChild')
                    ->where('priceSurcharge.id = ?1')
                    ->setParameter(1, $priceSurcharge->getId())
                    ->getQuery()
                    ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            $this->View()->assign(array(
                'success' => true,
                'data' => $data
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Event listener function of the article backend module. Fired when the user clicks the delete
     * button in the dependency window to delete a dependency.
     */
    public function deleteConfiguratorDependencyAction()
    {
        try {
            $id = (int) $this->Request()->getParam('id');
            if (empty($id)) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => 'No valid dependency id passed'
                ));
                return;
            }
            $model = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Dependency', $id);
            Shopware()->Models()->remove($model);
            Shopware()->Models()->flush();
            $this->View()->assign(array(
                'success' => true
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    public function deleteConfiguratorPriceSurchargeAction()
    {
        try {
            $id = (int) $this->Request()->getParam('id');
            if (empty($id)) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => 'No valid surcharge id passed'
                ));
                return;
            }
            $model = Shopware()->Models()->find('Shopware\Models\Article\Configurator\PriceSurcharge', $id);
            Shopware()->Models()->remove($model);
            Shopware()->Models()->flush();
            $this->View()->assign(array(
                'success' => true
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Internal helper function to save the article data.
     * @param $data
     * @param $article \Shopware\Models\Article\Article
     * @return mixed
     */
    protected function saveArticle($data, $article) {
        try {
            $data = $this->prepareAssociatedData($data, $article);
            $article->fromArray($data);

            Shopware()->Models()->persist($article);
            Shopware()->Models()->flush();
            if (empty($data['id']) && !empty($data['autoNumber'])) {
                $this->increaseAutoNumber($data['autoNumber'], $article->getMainDetail()->getNumber());
            }

            $savedArticle = $this->getArticle($article->getId());
            $this->View()->assign(array(
                'success' => true,
                'data' => $savedArticle
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }
    }

    /**
     * The loadStoresAction function is an ExtJs event listener method of the article backend module.
     * The function is used to load all required stores for the article detail page in one request.
     * @return array
     */
    public function loadStoresAction()
    {
        $id = $this->Request()->getParam('articleId');
        $priceGroups = $this->getRepository()->getPriceGroupQuery()->getArrayResult();
        $suppliers = $this->getRepository()->getSuppliersQuery()->getArrayResult();
        $shops = $this->getShopRepository()->createQueryBuilder('shops')->getQuery()->getArrayResult();
        $taxes = $this->getRepository()->getTaxesQuery()->getArrayResult();
        $templates = $this->getTemplates();
        $units = $this->getRepository()->getUnitsQuery()->getArrayResult();
        $customerGroups = $this->getCustomerRepository()->getCustomerGroupsQuery()->getArrayResult();
        $properties = $this->getRepository()->getPropertiesQuery()->getArrayResult();
        $configuratorGroups = $this->getRepository()->getConfiguratorGroupsQuery()->getArrayResult();
        $attributeFields = $this->getAttributeFields();

        $priceSurcharges = null;
        $dependencies = null;
        $configuratorSet = null;
        if (!empty($id)) {
            $article = $this->getArticle($id);
        } else {
            $article = $this->getNewArticleData();
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => array(
                'shops' => $shops,
                'customerGroups' => $customerGroups,
                'taxes' => $taxes,
                'suppliers' => $suppliers,
                'attributeFields' => $attributeFields,
                'templates' => $templates,
                'units' => $units,
                'properties' => $properties,
                'priceGroups' => $priceGroups,
                'article' => $article,
                'configuratorSet' => $configuratorSet,
                'configuratorGroups' => $configuratorGroups,
                'priceSurcharges' => $priceSurcharges,
                'dependencies' => $dependencies,
                'settings' => array()
            )
        ));
    }


    /**
     *
     */
    public function getPropertyListAction()
    {
        $articleId = $this->Request()->getParam('articleId');
        $propertyGroupId =  $this->Request()->getParam('propertyGroupId');

        $builder = Shopware()->Models()->createQueryBuilder()
            ->from('Shopware\Models\Property\Option', 'po')
            ->join('po.groups', 'pg', 'with', 'pg.id = :propertyGroupId')
            ->setParameter('propertyGroupId', $propertyGroupId)
            ->select(array( 'PARTIAL po.{id,name}' ));

        $query = $builder->getQuery();
        $options = array();
        foreach($query->getArrayResult() as $option) {
            $options[$option['id']] = $option;
        }

        $builder = Shopware()->Models()->createQueryBuilder()
            ->from('Shopware\Models\Property\Value', 'pv')
            ->join('pv.articles', 'pa', 'with', 'pa.id = :articleId')
            ->setParameter('articleId', $articleId)
            ->join('pv.option', 'po')
            ->select(array('po.id as optionId', 'pv.id as value'));

        $query = $builder->getQuery();
        foreach($query->getArrayResult() as $value) {
            if(!isset($options[$value['optionId']])) {
                continue;
            }
            if(!isset($options[$value['optionId']]['name'])) {
                $options[$value['optionId']]['value'] = array($value['value']);
            } else {
                $options[$value['optionId']]['value'][] = $value['value'];
            }
        }

        $this->View()->assign(array(
            'data' =>  array_values($options),
            'total' =>  count($options),
            'success' => true
        ));
    }

    /**
     * Returns the available property values
     */
    public function getPropertyValuesAction()
    {
        $propertyGroupId =  $this->Request()->getParam('propertyGroupId');

        $builder = Shopware()->Models()->createQueryBuilder()
            ->from('Shopware\Models\Property\Value', 'pv')
            ->join('pv.option', 'po')
            ->join('po.groups', 'pg', 'with', 'pg.id = :propertyGroupId')
            ->setParameter('propertyGroupId', $propertyGroupId)
            ->select(array( 'pv.id', 'pv.value', 'po.id as optionId' ));

        $query = $builder->getQuery();
        $data = $query->getArrayResult();

        $this->View()->assign(array(
            'data' =>  $data,
            'total' =>  count($data),
            'success' => true
        ));
    }

    public function setPropertyListAction()
    {
        $models = Shopware()->Models();
        $articleId = $this->Request()->getParam('articleId');
        /** @var $article Shopware\Models\Article\Article */
        $article = $models->find('Shopware\Models\Article\Article', $articleId);
        $properties = $this->Request()->getParam('properties', array());

        if (empty($properties[0])){
            $properties[0] = array(
                "id" => $this->Request()->getParam('id'),
                "name" => $this->Request()->getParam('name'),
                "value" => $this->Request()->getParam('value'),

            );
        }

        $propertyValues = $article->getPropertyValues();
        $propertyValues->clear();
        $models->flush();

        // If no property group is set for the article, don't recreate the property values
        $propertyGroup = $article->getPropertyGroup();
        if(!$propertyGroup) {
            $this->View()->assign(array(
                'success' => true
            ));
            return;
        }

        // recreate property values
        foreach($properties as $property) {
            if(empty($property['value'])) {
                continue;
            }
            /** @var $article Shopware\Models\Property\Option */
            $option = $models->find('Shopware\Models\Property\Option', $property['id']);
            foreach((array) $property['value'] as $value) {
                if(is_int($value)) {
                    $value = $models->find('Shopware\Models\Property\Value', $value);
                } else {
                    $value = new Shopware\Models\Property\Value(
                        $option,
                        $value
                    );
                    $models->persist($value);
                }
                if($value !== null) {
                    $propertyValues->add($value);
                }
            }
        }
        $models->flush();

        $this->View()->assign(array(
            'success' => true
        ));
    }

    /**
     * Selects the dynamic attribute fields
     * @return array
     */
    protected function getAttributeFields()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        return $builder->select(array('elements'))
                ->from('Shopware\Models\Article\Element', 'elements')
                ->orderBy('elements.position')
                ->getQuery()
                ->getArrayResult();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     * @param $articleId
     * @return array
     */
    protected function getArticleData($articleId)
    {
        return $this->getRepository()
                    ->getArticleBaseDataQuery($articleId)
                    ->getArrayResult();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     * @param $articleId
     * @return array
     */
    public function getArticleCategories($articleId)
    {
        $result = $this->getRepository()
                ->getArticleCategoriesQuery($articleId)
                ->getArrayResult();

        if (empty($result[0]['categories'])) {
            return array();
        }

        $sql= "SELECT categoryID FROM s_articles_categories WHERE articleID = ? ORDER BY id ASC";
        $categorySort = Shopware()->Db()->fetchCol($sql, array($articleId));

        $categories = array();
        foreach($categorySort as $sort) {
            $category = $result[0]['categories'][$sort];
            $category['name'] = $this->getCategoryRepository()->getPathById($sort, 'name', '>');
            $categories[] = $category;
        }
        return $categories;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     * @param $articleId
     * @return array
     */
    public function getArticleSimilars($articleId)
    {
        $result = $this->getRepository()
                ->getArticleSimilarsQuery($articleId)
                ->getArrayResult();

        if (empty($result[0]['similar'])) {
            return array();
        } else {
            return $result[0]['similar'];
        }
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     * @param $articleId
     * @return array
     */
    public function getArticleRelated($articleId)
    {
        $result = $this->getRepository()
                ->getArticleRelatedQuery($articleId)
                ->getArrayResult();

        if (empty($result[0]['related'])) {
            return array();
        } else {
            return $result[0]['related'];
        }
    }


    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     * @param $articleId
     * @return array
     */
    public function getArticleImages($articleId)
    {
        $result = $this->getRepository()
                ->getArticleWithImagesQuery($articleId)
                ->getArrayResult();

        if (empty($result[0]['images'])) {
            return array();
        } else {
            return $result[0]['images'];
        }
    }


    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     * @param $articleId
     * @return array
     */
    public function getArticleLinks($articleId)
    {
        $result = $this->getRepository()
                ->getArticleLinksQuery($articleId)
                ->getArrayResult();

        if (empty($result[0]['links'])) {
            return array();
        } else {
            // map the link target to the boolean format that is expected by the ExtJS backend module
            $links = $result[0]['links'];
            foreach($links as &$linkData) {
                $linkData['target'] = ($linkData['target'] === "_blank") ? true : false;
            }
        }

        return $links;
    }


    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     * @param $articleId
     * @return array
     */
    public function getArticleDownloads($articleId)
    {
        $result = $this->getRepository()
                ->getArticleDownloadsQuery($articleId)
                ->getArrayResult();

        if (empty($result[0]['downloads'])) {
            return array();
        } else {
            return $result[0]['downloads'];
        }
    }


    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     * @param $articleId
     * @return array
     */
    public function getArticleCustomerGroups($articleId)
    {
        $result = $this->getRepository()
                ->getArticleCustomerGroupsQuery($articleId)
                ->getArrayResult();

        if (empty($result[0]['customerGroups'])) {
            return array();
        } else {
            return $result[0]['customerGroups'];
        }
    }


    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     * @param $articleId
     * @return array
     */
    public function getArticleConfiguratorSet($articleId)
    {
        $result = $this->getRepository()
                ->getArticleConfiguratorSetQuery($articleId)
                ->getArrayResult();

        if (empty($result[0]['configuratorSet'])) {
            return array();
        } else {
            return $result[0]['configuratorSet'];
        }
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $configuratorSetId
     * @return array
     */
    public function getArticleDependencies($configuratorSetId)
    {
        return $this->getRepository()
                    ->getConfiguratorDependenciesQuery($configuratorSetId)
                    ->getArrayResult();

    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $configuratorSetId
     * @return array
     */
    public function getArticlePriceSurcharges($configuratorSetId)
    {
        return $this->getRepository()
                    ->getConfiguratorPriceSurchargesQuery($configuratorSetId)
                    ->getArrayResult();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $articleId
     * @param $tax
     *
     * @return array
     */
    public function getArticleConfiguratorTemplate($articleId, $tax)
    {
        $query = $this->getRepository()->getConfiguratorTemplateByArticleIdQuery($articleId);

        $configuratorTemplate = $query->getArrayResult();

        $prices = $configuratorTemplate[0]['prices'];

        if (!empty($prices)) {
            $configuratorTemplate[0]['prices'] = $this->formatPricesFromNetToGross($prices, $tax);
        }

        return $configuratorTemplate;
    }

    /**
     * Helper function to get a one or null result over the pagination extension
     * @param $query \Doctrine\ORM\Query
     *
     * @return array
     */
    private function getOneOrNullResult($query)
    {
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        return $paginator->getIterator()->getArrayCopy();
    }


    /**
     * Internal helper function to get the article data of the passed id.
     * @param $id
     * @return array
     */
    protected function getArticle($id)
    {
        $data = $this->getArticleData($id);

        $data[0]['attribute'] = $data[0]['mainDetail']['attribute'];
        $tax = $data[0]['tax'];

        $data[0]['categories'] = $this->getArticleCategories($id);
        $data[0]['similar'] = $this->getArticleSimilars($id);
        $data[0]['related'] = $this->getArticleRelated($id);
        $data[0]['images'] = $this->getArticleImages($id);
        $data[0]['links'] = $this->getArticleLinks($id);
        $data[0]['downloads'] = $this->getArticleDownloads($id);
        $data[0]['customerGroups'] = $this->getArticleCustomerGroups($id);
        $data[0]['mainPrices'] = $this->getPrices($data[0]['mainDetail']['id'], $tax);
        $data[0]['configuratorSet'] = $this->getArticleConfiguratorSet($id);
        $data[0]['dependencies'] = array();
        $data[0]['priceSurcharges'] = array();

        if (!empty($data[0]['configuratorSetId'])) {
            $data[0]['dependencies'] = $this->getArticleDependencies($data[0]['configuratorSetId']);
            $data[0]['priceSurcharges'] = $this->getArticlePriceSurcharges($data[0]['configuratorSetId']);
        }

        $data[0]['configuratorTemplate'] = $this->getArticleConfiguratorTemplate($id, $tax);

        if ($data[0]['added'] && $data[0]['added'] instanceof \DateTime) {
            $added = $data[0]['added'];
            $data[0]['added'] = $added->format('d.m.Y');
        }

        return $data;
    }

    /**
     * Loads the variant listing for the article backend module.
     */
    public function detailListAction()
    {
        if (!$this->Request()->has('articleId')) {
            $this->View()->assign(array(
                'success' => false,
                'message' => 'No valid article id passed'
            ));
            return;
        }
        $articleId = $this->Request()->getParam('articleId');

        /**@var $article \Shopware\Models\Article\Article*/
        $article = Shopware()->Models()->find('Shopware\Models\Article\Article', $articleId);
        $tax = array(
            'tax' => $article->getTax()->getTax()
        );

        $idQuery = $this->getRepository()->getConfiguratorListIdsQuery(
            $articleId,
            $this->Request()->getParam('filter'),
            $this->Request()->getParam('sort'),
            $this->Request()->getParam('start'),
            $this->Request()->getParam('limit', 20)
        );

        $total = Shopware()->Models()->getQueryCount($idQuery);
        $ids = $idQuery->getArrayResult();

        foreach($ids as $key => $id) {
            $ids[$key] = $id['id'];
        }
        if (empty($ids)) {
            $this->View()->assign(array(
                'success' => true,
                'data' => array(),
                'total' => 0
            ));
            return;
        }

        $query = $this->getRepository()->getDetailsByIdsQuery($ids, $this->Request()->getParam('sort'));
        $details = $query->getArrayResult();

        $return = array();
        foreach($details as $key => $detail) {
            if (empty($detail['prices']) || empty($detail['configuratorOptions'])) {
                continue;
            }
            $detail['prices'] = $this->formatPricesFromNetToGross($detail['prices'], $tax);
            if ($detail['releaseDate']) {
                $releaseDate = $detail['releaseDate'];
                if ($releaseDate instanceof \DateTime) {
                    $detail['releaseDate'] = $releaseDate->format('d.m.Y');
                }
            }
            $return[] = $detail;
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $return,
            'total' => $total
        ));
    }

    /**
     * Internal helper function to convert gross prices to net prices.
     * @param $prices
     * @param $tax
     * @return array
     */
    protected function formatPricesFromNetToGross($prices, $tax)
    {
        foreach ($prices as $key => $price) {
            $customerGroup = $price['customerGroup'];
            if ($customerGroup['taxInput']) {
                $price['price'] = $price['price'] / 100 * (100 + $tax['tax']) ;
                $price['pseudoPrice'] = $price['pseudoPrice'] / 100 * (100 + $tax['tax']) ;
            }
            $prices[$key] = $price;
        }
        return $prices;
    }


    /**
     * Internal helper function to load the article main detail prices into the backend module.
     * @param $id
     * @param $tax
     * @return array
     */
    protected function getPrices($id, $tax)
    {
        $prices = $this->getRepository()
                       ->getPricesQuery($id)
                       ->getArrayResult();

        return $this->formatPricesFromNetToGross($prices, $tax);
    }

    /**
     * Saves the passed configurator group data. If an id passed, the function updates the existing group, otherwise
     * a new group will be created.
     */
    public function saveConfiguratorGroupAction()
    {
        try {
            $id = (int)$this->Request()->getParam('id');
            if (!empty($id)) {
                $group = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Group', $id);
            } else {
                $group = new \Shopware\Models\Article\Configurator\Group();
            }
            $data = $this->Request()->getParams();
            unset($data['options']);
            $group->fromArray($data);
            Shopware()->Models()->persist($group);
            Shopware()->Models()->flush();
            $data['id'] = $group->getId();

            $this->View()->assign(array(
                'success' => true,
                'data' => $data
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Saves the passed configurator option data. If an id passed, the function updates the existing options, otherwise
     * a new option will be created.
     */
    public function saveConfiguratorOptionAction()
    {
        try {
            $id = (int) $this->Request()->getParam('id');
            if (!empty($id)) {
                $option = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Option', $id);
            } else {
                $option = new \Shopware\Models\Article\Configurator\Option();
            }
            $data = $this->Request()->getParams();
            if (empty($data['groupId'])) {
                return;
            }
            $data['group'] = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Group', $data['groupId']);

            $option->fromArray($data);
            Shopware()->Models()->persist($option);
            Shopware()->Models()->flush();
            $data['id'] = $option->getId();

            $this->View()->assign(array(
                'success' => true,
                'data' => $data
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Helper function which creates for the passed configurator groups
     * the cross join sql for all possible variants.
     * Returns an array with the sql and all used group ids
     * @param $groups
     * @param $offset
     * @param $limit
     * @return array
     */
    protected function prepareGeneratorData($groups, $offset, $limit)
    {

        //we have to iterate all passed groups to check the activated options.
        $activeGroups = array();
        //we need a second array with all group ids to iterate them easily in the sql generation
        $originals = array();
        $allOptions = array();

        $groupPositions = array();

        foreach($groups as $group) {
            if (!$group['active']) {
                continue;
            }

            $options = array();
            //we iterate the options to get the option ids in a one dimensional array.
            foreach($group['options'] as $option) {
                if ($option['active']) {
                    $options[] = $option['id'];
                    $allOptions[$option['id']] = $option['id'];
                }
            }

            //if some options active, we save the group and the options in an internal array
            if (!empty($options)) {
                $activeGroups[] = array('id' => $group['id'], 'options' => $options);
                $groupPositions[$group['id']] = (int) $group['position'];
                $originals[] = $group['id'];
            }
        }
 
        if (empty($activeGroups)) {
            return array();
        }

        //the first groups serves as the sql from path, so we have to remove the first id from the array
        $first = array_shift($activeGroups);
        $firstId = $first['id'];

        //now we create plain sql templates to parse the ids over the sprintf function
        $selectTemplate = "o%s.id as o%sId, o%s.name as o%sName, g%s.id as g%sId, g%s.name as g%sName, o%s.position as o%sPosition, g%s.position as g%sPosition ";

        $fromTemplate = "FROM s_article_configurator_options o%s
                            LEFT JOIN s_article_configurator_groups g%s ON g%s.id = o%s.group_id";

        $joinTemplate = "CROSS JOIN s_article_configurator_options o%s ON o%s.group_id = %s AND o%s.id IN (%s)
                            LEFT JOIN s_article_configurator_groups g%s ON g%s.id = o%s.group_id";

        $whereTemplate = "WHERE o%s.group_id = %s
                          AND o%s.id IN (%s)";

        asort($groupPositions);
        $orders = array();
        foreach($groupPositions as $id => $position) {
            $orders[] = 'g' . $id . 'Position, o' . $id . 'Position';
        }
        $orderBy = ' ORDER BY ' . implode(' , ', $orders);

        $groupSql = array();
        $selectSql = array();

        //we have remove the first group id, but we need the first id in the select, from and where path.
        $selectSql[] = sprintf($selectTemplate, $firstId,$firstId,$firstId,$firstId,$firstId,$firstId,$firstId,$firstId,$firstId,$firstId,$firstId,$firstId);
        $groupSql[] = sprintf($fromTemplate, $firstId,$firstId,$firstId,$firstId);
        $whereSql = sprintf($whereTemplate, $firstId,$firstId,$firstId, implode(',', $first['options']));

        //now we iterate all other groups, and create a select sql path and a cross join sql path.
        foreach($activeGroups as $group) {
            $groupId = $group['id'];
            $selectSql[] = sprintf($selectTemplate, $groupId,$groupId,$groupId,$groupId,$groupId,$groupId,$groupId,$groupId,$groupId,$groupId,$groupId,$groupId);
            $groupSql[] = sprintf($joinTemplate, $groupId,$groupId,$groupId,$groupId,implode(',', $group['options']),$groupId,$groupId,$groupId);
        }

        //concat the sql statement
        $sql= 'SELECT ' . implode(",\n", $selectSql) . ' ' . implode("\n", $groupSql) . ' ' . $whereSql . $orderBy .  ' LIMIT ' . $offset . ',' . $limit;

        return array(
            'sql' => $sql,
            'originals' => $originals,
            'allOptions' => $allOptions
        );
    }

    private function setDetailDataReferences($detailData, $article) {
        foreach($detailData['prices'] as &$price) {
            $price['article'] = $article;
            unset($price['id']);
            $price['customerGroup'] = Shopware()->Models()->find('Shopware\Models\Customer\Group', $price['customerGroup']['id']);
        }
        if ($detailData['unitId']) {
            $detailData['unit'] = Shopware()->Models()->find('Shopware\Models\Article\Unit', $detailData['unitId']);
        }

        if (!empty($detailData['attribute'])) {
            unset($detailData['attribute']['id']);
            unset($detailData['attribute']['articleId']);
            unset($detailData['attribute']['articleDetailId']);
            unset($detailData['attribute']['articleDetail']);
            $detailData['attribute']['article'] = $article;
        }
        return $detailData;
    }

    /**
     * Called when the user clicks the "generateVariants" button in the article backend module.
     * The function expects that an article id passed and an array with active groups passed.
     */
    public function createConfiguratorVariantsAction()
    {
        try {
            //first get the id parameter of the request object
            $articleId = $this->Request()->getParam('articleId', 1);
            $groups = $this->Request()->getParam('groups');
            $offset = $this->Request()->getParam('offset', 0);
            $limit = $this->Request()->getParam('limit', 50);

            //the merge type defines if all variants has to been regenerated or if only new variants will be added.
            //1 => Regenerate all variants
            //2 => Merge variants
            $mergeType = $this->Request()->getParam('mergeType', 1);

            /**@var $article \Shopware\Models\Article\Article*/
            $article = $this->getRepository()->find($articleId);

            $generatorData = $this->prepareGeneratorData($groups, $offset, $limit);

            $detailData = $this->getDetailDataForVariantGeneration($article);

            if ($offset === 0 && $mergeType == 1) {
                $this->removeAllConfiguratorVariants($articleId);
            } else if ($offset === 0 && $mergeType == 2) {
                $this->deleteVariantsForAllDeactivatedOptions($article, $generatorData['allOptions']);
            }

            Shopware()->Models()->clear();
            $article = $this->getRepository()->find($articleId);
            $detailData = $this->setDetailDataReferences($detailData, $article);

            $configuratorSet = $article->getConfiguratorSet();
            $dependencies = $this->getRepository()->getConfiguratorDependenciesQuery($configuratorSet->getId())->getArrayResult();
            $priceSurcharges = $this->getRepository()->getConfiguratorPriceSurchargesQuery($configuratorSet->getId())->getArrayResult();

            if (empty($generatorData)) {
                return;
            }

            $sql = $generatorData['sql'];
            $originals = $generatorData['originals'];
            $variants = Shopware()->Db()->fetchAll($sql);

            $counter = 1;
            if ($mergeType === 1) {
                $counter = $offset;
            }
            $allOptions = $this->getRepository()->getAllConfiguratorOptionsIndexedByIdQuery()->getResult();

            //iterate all selected variants to insert them into the database
            foreach($variants as $variant) {
                $variantData = $this->prepareVariantData($variant, $detailData, $counter, $dependencies, $priceSurcharges, $allOptions, $originals, $article, $mergeType);
                if ($variantData === false) {
                    continue;
                }

                //merge the data with the original main detail data
                $data = array_merge($detailData, $variantData);

                //use only the main detail of the article as base object, if the merge type is set to "Override" and the current variant is the first generated variant.
                if ($offset === 0 && $mergeType === 1) {
                    $detail = $article->getMainDetail();
                } else {
                    $detail = new \Shopware\Models\Article\Detail();
                    Shopware()->Models()->persist($detail);
                }

                $detail->fromArray($data);
                $detail->setArticle($article);
                $offset++;
            }

            Shopware()->Models()->flush();

            //check if the main detail variant was deleted
            if ($article->getMainDetail() === null) {
                $newMainDetail = $this->getArticleDetailRepository()->findOneBy(array('articleId' => $articleId));
                $article->setMainDetail($newMainDetail);
            }

            Shopware()->Models()->flush();

            $article = $this->getArticle($articleId);
            $this->View()->assign(array(
                'success' => true,
                'data' => $article
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }


    /**
     * Internal helper function to remove all article variants for the deselected options.
     * @param \Shopware\Models\Article\Article $article
     * @param array $selectedOptions
     *
     */
    protected  function deleteVariantsForAllDeactivatedOptions($article, $selectedOptions)
    {
        $configuratorSet = $article->getConfiguratorSet();
        $oldOptions = $configuratorSet->getOptions();
        $ids = array();
        /**@var $oldOption \Shopware\Models\Article\Configurator\Option*/
        foreach($oldOptions as $oldOption) {
            if (!array_key_exists($oldOption->getId(), $selectedOptions)) {
                $details = $this->getRepository()
                                ->getArticleDetailByConfiguratorOptionIdQuery($article->getId(), $oldOption->getId())
                                ->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT)
                                ->getResult();

                if (!empty($details)) {
                    /**@var $detail \Shopware\Models\Article\Detail*/
                    foreach($details as $detail) {
                        if ($detail->getKind() === 1) {
                            $article->setMainDetail(null);
                        }
                        $ids[] = $detail->getId();
                        Shopware()->Models()->remove($detail);
                    }
                    Shopware()->Models()->flush();
                }
            }
        }

        if (!empty($ids)) {
            $builder = Shopware()->Models()->createQueryBuilder();
            $builder->delete('Shopware\Models\Attribute\Article', 'attribute')
                    ->where('attribute.articleDetailId IN (:articleDetailIds)')
                    ->setParameters(array('articleDetailIds' => $ids))
                    ->getQuery()
                    ->execute();
        }

    }


    /**
     * Helper function to prepare the variant data for a new article detail.
     * Iterates all passed price surcharges and dependencies to check if the current variant
     * has configurator options which defined in the dependencies or in the price surcharges.
     * The used price surcharge options will be added to each variant price row.
     * If the variant has configurator options which defined as dependency row,
     * the variant won't be created. The function will return false and
     * the foreach queue in the "createConfiguratorVariantsAction" will be continue.
     *
     * @param $variant
     * @param $detailData
     * @param $counter
     * @param $dependencies
     * @param $priceSurcharges
     * @param $allOptions
     * @param $originals
     * @param $article \Shopware\Models\Article\Article
     * @param $mergeType
     *
     * @return array|bool
     */
    protected function prepareVariantData($variant, $detailData, &$counter, $dependencies, $priceSurcharges, $allOptions, $originals, $article, $mergeType) {
        $name = '';
        $optionsModels= array();
        $tax = $article->getTax();
        $optionIds = array();


        //iterate the original ids to get the new variant name
        foreach($originals as $id) {
            $optionId = $variant['o' . $id . 'Id'];

            //first we push the option ids in an one dimensional array to check
            $optionIds[] = $optionId;

            $optionsModels[] = $allOptions[$optionId];
            $name[] = $variant['o' . $id . 'Name'];
        }

        $optionPriceSurcharges = array();
        foreach($priceSurcharges as $surcharge) {
            if (in_array($surcharge['parentId'], $optionIds) && empty($surcharge['childId'])) {
                $optionPriceSurcharges[] = $surcharge;
            } elseif (in_array($surcharge['parentId'], $optionIds) && !empty($surcharge['childId']) && in_array($surcharge['childId'], $optionIds)) {
                $optionPriceSurcharges[] = $surcharge;
            }
        }

        $abortVariant = false;
        foreach($dependencies as $dependency) {
            if (in_array($dependency['parentId'], $optionIds) && in_array($dependency['childId'], $optionIds)) {
                $abortVariant = true;
            }
        }

        //if the user selects the "merge variants" generation type, we have to check if the current variant already exist.
        if ($mergeType === 2 && $abortVariant === false) {
            $query = $this->getRepository()->getDetailsForOptionIdsQuery($article->getId(), $optionsModels);
            $exist = $query->getArrayResult();
            $abortVariant = !empty($exist);
        }

        if ($abortVariant) {
            return false;
        }

        //create the new variant data
        $variantData = array(
            'additionalText' => implode(' / ', $name),
            'active' => 1,
            'configuratorOptions' => $optionsModels
        );


        if ($mergeType == 1 && $counter == 0) {
            $variantData['number'] = $detailData['number'];
            $counter++;
        } else {
            do {
                $variantData['kind'] = 2;
                $variantData['number'] = $detailData['number'] . '.' . $counter;
                $counter++;
            } while($this->orderNumberExist($variantData['number']));
        }

        //we have to check the defined price surcharges for the article configurator set,
        //to add the defined surcharges to the variant prices with the corresponding configurator options
        if (!empty($optionPriceSurcharges)) {
            $fullPriceSurcharge = 0;
            foreach($optionPriceSurcharges as $priceSurcharge) {
                $fullPriceSurcharge += $priceSurcharge['surcharge'];
            }

            $prices = $detailData['prices'];
            $newPrices = array();

            foreach($prices as $priceData) {
                $priceData['price'] += $fullPriceSurcharge;
                $newPrices[] = $priceData;
            }
            $variantData['prices'] = $newPrices;
        }
        return $variantData;
    }

    private function orderNumberExist($number)
    {
        $detail = $this->getArticleDetailRepository()->findOneBy(array(
            'number' => $number
        ));

        return !empty($detail);
    }

    protected function getDependencyByOptionId($optionId, $dependencies) {
        $returnValue = array();
        foreach($dependencies as $dependency) {
            if ($dependency['parentId'] == $optionId) {
                $returnValue = $dependency;
                break;
            }
        }
        return $returnValue;
    }

    protected function getSurchargeByOptionId($optionId, $priceSurcharges) {
        $returnValue = array();
        foreach($priceSurcharges as $priceSurcharge) {
            if ($priceSurcharge['parentId'] == $optionId) {
                $returnValue = $priceSurcharge;
                break;
            }
        }
        return $returnValue;
    }


    /**
     * Helper function for the variant generation. Returns the article main detail data which used as base configuration for
     * the generated article variants.
     * @param $article
     * @return mixed
     */
    protected function getDetailDataForVariantGeneration($article)
    {
        $detailData = $this->getRepository()
                           ->getConfiguratorTemplateByArticleIdQuery($article->getId())
                           ->getArrayResult();

        if (empty($detailData)) {
            $this->createConfiguratorTemplate($article);
            $detailData = $this->getRepository()
                    ->getConfiguratorTemplateByArticleIdQuery($article->getId())
                    ->getArrayResult();

        }
        return $detailData[0];
    }

    /**
     * @param $article \Shopware\Models\Article\Article
     */
    protected function createConfiguratorTemplate($article)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('detail', 'prices', 'customerGroup', 'attribute', 'priceAttribute'))
                ->from('Shopware\Models\Article\Detail', 'detail')
                ->leftJoin('detail.prices', 'prices')
                ->leftJoin('prices.customerGroup', 'customerGroup')
                ->leftJoin('detail.attribute', 'attribute')
                ->leftJoin('prices.attribute', 'priceAttribute')
                ->where('detail.id = :id')
                ->setParameters(array('id' => $article->getMainDetail()->getId()));

        $data = $builder->getQuery()->getArrayResult();
        $data = $data[0];

        foreach($data['prices'] as &$price) {
            $customerGroup = $this->getCustomerGroupRepository()->find($price['customerGroup']['id']);
            $price['customerGroup'] = $customerGroup;
        }

        $template = new \Shopware\Models\Article\Configurator\Template\Template();
        $template->fromArray($data);
        $template->setArticle($article);

        Shopware()->Models()->persist($template);
        Shopware()->Models()->flush();
    }


    /**
     * This function prepares the posted extJs data. First all ids resolved to the assigned shopware models.
     * After the ids resolved, the function removes the two dimensional arrays of oneToOne associations.
     * @param $data
     * @param $article \Shopware\Models\Article\Article
     * @return array
     */
    protected function prepareAssociatedData($data, $article)
    {
        //format the posted extJs article data
        $data = $this->prepareArticleAssociatedData($data);

        //format the posted extJs article main detail data
        $data = $this->prepareMainDetailAssociatedData($data);

        //format the posted extJs article main prices data
        $data = $this->prepareMainPricesAssociatedData($data, $article);

        $data = $this->prepareAvoidCustomerGroups($data);

        //format the posted extJs article configurator association.
        $data = $this->prepareConfiguratorAssociatedData($data, $article);

        //format the posted extJs article attribute data
        $data = $this->prepareAttributeAssociatedData($data, $article);

        //format the posted extJs article categories associations
        $data = $this->prepareCategoryAssociatedData($data);

        //format the posted extJs related article association
        $data = $this->prepareRelatedAssociatedData($data, $article);

        //format the posted extJs similar article association
        $data = $this->prepareSimilarAssociatedData($data, $article);

        //format the posted extJs article image data
        $data = $this->prepareImageAssociatedData($data);

        //format the posted extJs article link data
        $data = $this->prepareLinkAssociatedData($data);

        //format the posted extJs article download data
        $data = $this->prepareDownloadAssociatedData($data);

        $data = $this->prepareConfiguratorTemplateData($data, $article);
        return $data;
    }

    /**
     * Internal helper function which resolves the passed configurator template foreign keys
     * with the associated models.
     * @param $data
     * @param $article
     *
     * @return mixed
     */
    protected function prepareConfiguratorTemplateData($data, $article)
    {
        if (empty($data['configuratorTemplate'])) {
            unset($data['configuratorTemplate']);
            return $data;
        }
        $data['configuratorTemplate'] = $data['configuratorTemplate'][0];
        $data['configuratorTemplate']['attribute'] = $data['configuratorTemplate']['attribute'][0];

        if (empty($data['configuratorTemplate'])) {
            $data['configuratorTemplate'] = null;
            return $data;
        }

        if (!empty($data['configuratorTemplate']['unitId'])) {
            $data['configuratorTemplate']['unit'] = Shopware()->Models()->find('Shopware\Models\Article\Unit', $data['configuratorTemplate']['unitId']);
        } else {
            $data['configuratorTemplate']['unit'] = null;
        }

        $data['configuratorTemplate']['prices'] = $this->preparePricesAssociatedData($data['configuratorTemplate']['prices'], $article, $data['tax']);
        $data['configuratorTemplate']['article'] = $article;
        return $data;
    }

    /**
     * Internal helper function which resolves the passed customer group ids
     * with Shopware\Models\Customer\Group models.
     * The configured customer groups are not allowed to set the article in the store front.
     * @param $data
     * @return mixed
     */
    protected function prepareAvoidCustomerGroups($data) {
        if (!empty($data['customerGroups'])) {
            $customerGroups = array();
            foreach($data['customerGroups'] as $customerGroup) {
                if (!empty($customerGroup['id'])) {
                    $customerGroups[] = Shopware()->Models()->find('Shopware\Models\Customer\Group', $customerGroup['id']);
                }
            }
            $data['customerGroups'] = $customerGroups;
        } else {
            $data['customerGroups'] = null;
        }

        return $data;
    }

    /**
     * Internal helper function to check if the article is configured as
     * multiple dimensional article (Configurator activated).
     * The following scenarios are possible:
     * <code>
     *  - New Article
     *    --> Checkbox activated
     *    --> "isConfigurator" = true  / configuratorSetId = null
     *    --> A new configurator set will be created with the name "Set-ArticleNumber"
     *
     *  - Existing Article
     *    --> Checkbox wasn't activated before, now the user activated the checkbox
     *    --> "isConfigurator" = true  / configuratorSetId = null
     *    --> A new configurator set will be created with the name "Set-ArticleNumber"
     *
     *  - Existing Article
     *    --> Checkbox was activated before, now the user deactivated the checkbox
     *    --> "isConfigurator" = false / configuratorSetId = Some Numeric value
     *    --> The old configurator set will be deleted.
     *
     * </code>
     * @param $data
     * @param $article \Shopware\Models\Article\Article
     * @return array
     */
    protected function prepareConfiguratorAssociatedData($data, $article)
    {
        if (!empty($data['configuratorSetId'])) {
            $data['configuratorSet'] = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Set', $data['configuratorSetId']);
        } else if ($data['isConfigurator']) {
            $set = new \Shopware\Models\Article\Configurator\Set();
            $set->setName('Set-' . $data['mainDetail']['number']);
            $set->setPublic(false);
            $data['configuratorSet'] = $set;
        } else {
            //if the article has an configurator set, we have to remove this set if it isn't used for other articles
            if ($article->getConfiguratorSet() && $article->getConfiguratorSet()->getId()) {
                $builder = Shopware()->Models()->createQueryBuilder();
                $articles = $builder->select(array('articles'))
                        ->from('Shopware\Models\Article\Article', 'articles')
                        ->where('articles.configuratorSetId = ?1')
                        ->setParameter(1, $article->getConfiguratorSet()->getId())
                        ->getQuery()
                        ->getArrayResult();

                if (count($articles) <= 1) {
                    $set = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Set', $article->getConfiguratorSet()->getId());
                    Shopware()->Models()->remove($set);
                }
            }
            $data['configuratorSet'] = null;
        }
        return $data;
    }

    /**
     * This function prepares the posted extJs data of the article model.
     * @param $data
     * @return array
     */
    protected function prepareArticleAssociatedData($data)
    {
        //check if a tax id is passed and load the tax model or set the tax parameter to null.
        if (!empty($data['taxId'])) {
            $data['tax'] = Shopware()->Models()->find('Shopware\Models\Tax\Tax', $data['taxId']);
        } else {
            $data['tax'] = null;
        }

        //check if a supplier id is passed and load the supplier model or set the supplier parameter to null.
        if (!empty($data['supplierId'])) {
            $data['supplier'] = Shopware()->Models()->find('Shopware\Models\Article\Supplier', $data['supplierId']);
        } elseif (!empty($data['supplierName'])) {
            $supplier = $this->getManager()->getRepository('Shopware\Models\Article\Supplier')->findOneBy(array('name' => trim($data['supplierName'])));
            if (!$supplier) {
                $supplier = new \Shopware\Models\Article\Supplier();
                $supplier->setName($data['supplierName']);
            }
            $data['supplier'] = $supplier;
        } else {
            $data['supplier'] = null;
        }

        //check if a supplier id is passed and load the supplier model or set the supplier parameter to null.
        if (!empty($data['priceGroupId'])) {
            $data['priceGroup'] = Shopware()->Models()->find('Shopware\Models\Price\Group', $data['priceGroupId']);
        } else {
            $data['priceGroup'] = null;
        }

        if (!empty($data['filterGroupId'])) {
            $data['propertyGroup'] = Shopware()->Models()->find('Shopware\Models\Property\Group', $data['filterGroupId']);
        } else {
            $data['propertyGroup'] = null;
        }

        $data['changed'] = new \DateTime();
        return $data;
    }

    /**
     * Prepares the data for the article main detail object.
     * @param $data
     * @return array
     */
    protected function prepareMainDetailAssociatedData($data)
    {
        $data['mainDetail'] = $data['mainDetail'][0];
        $data['mainDetail']['active'] = $data['active'];
        if (!empty($data['mainDetail']['unitId'])) {
            $data['mainDetail']['unit'] = Shopware()->Models()->find('Shopware\Models\Article\Unit', $data['mainDetail']['unitId']);
        } else {
            $data['mainDetail']['unit'] = null;
        }
        unset($data['mainDetail']['configuratorOptions']);
        return $data;
    }

    /**
     * This function loads the category models for the passed ids in the "categories" parameter.
     * @param $data
     * @return array
     */
    protected function prepareCategoryAssociatedData($data)
    {
        $categories = array();
        foreach($data['categories'] as $categoryData) {
            if (!empty($categoryData['id'])) {
                $model = Shopware()->Models()->find('Shopware\Models\Category\Category', $categoryData['id']);
                $categories[] = $model;
            }
        }
        $data['categories'] = $categories;
        return $data;
    }

    /**
     * This function loads the related article models for the passed ids in the "related" parameter.
     * @param $data
     * @param $article \Shopware\Models\Article\Article
     * @return array
     */
    protected function prepareRelatedAssociatedData($data, $article)
    {
        $related = array();
        foreach($data['related'] as $relatedData) {
            if (empty($relatedData['id'])) {
                continue;
            }
            /**@var $relatedArticle \Shopware\Models\Article\Article*/
            $relatedArticle = $this->getRepository()->find($relatedData['id']);

            //if the user select the cross
            if ($relatedData['cross']) {
                $relatedArticle->getRelated()->add($article);
                Shopware()->Models()->persist($relatedArticle);
            }
            $related[] = $relatedArticle;
        }
        $data['related'] = $related;
        return $data;
    }


    /**
     * This function loads the similar models for the passed ids in the "similar" parameter.
     * @param $data
     * @param $article \Shopware\Models\Article\Article
     * @return array
     */
    protected function prepareSimilarAssociatedData($data, $article)
    {
        $similar = array();
        foreach($data['similar'] as $similarData) {
            if (empty($similarData['id'])) {
                continue;
            }
            /**@var $similarArticle \Shopware\Models\Article\Article*/
            $similarArticle = $this->getRepository()->find($similarData['id']);

            //if the user select the cross
            if ($similarData['cross']) {
                $similarArticle->getSimilar()->add($article);
                Shopware()->Models()->persist($similarArticle);
            }
            $similar[] = $similarArticle;
        }
        $data['similar'] = $similar;
        return $data;
    }

    /**
     * This function loads the category models for the passed ids in the "categories" parameter.
     * @param $data
     * @return array
     */
    protected function prepareImageAssociatedData($data)
    {
        $position = 1;
        foreach($data['images'] as &$imageData) {
            $imageData['position'] = $position;
            $imageData['attribute'] = $imageData['attribute'][0];
            if (!empty($imageData['mediaId'])) {
                $media = Shopware()->Models()->find('Shopware\Models\Media\Media', $imageData['mediaId']);
                if ($media instanceof \Shopware\Models\Media\Media) {
                    $imageData['media'] = $media;
                } else {
                    $imageData['media'] = null;
                }
            } else {
                $imageData['media'] = null;
            }
            unset($imageData['mappings']);
            unset($imageData['children']);
            unset($imageData['parent']);
            $position++;
        }
        return $data;
    }

    /**
     * This function prepares the attribute data for the article.
     * @param $data
     * @param $article
     * @return array
     */
    protected function prepareAttributeAssociatedData($data, $article)
    {
        $data['attribute'][0]['article'] = $article;
        $data['mainDetail']['attribute'] = $data['attribute'][0];
        unset($data['attribute']);
        return $data;
    }

    /**
     * This function prepares the prices for the article main detail object.
     * @param $data
     * @param $article \Shopware\Models\Article\Article
     * @return mixed
     */
    protected function prepareMainPricesAssociatedData($data, $article)
    {
        $data['mainDetail']['prices'] = $this->preparePricesAssociatedData($data['mainPrices'], $article, $data['tax']);
        return $data;
    }

    /**
     * @param $prices array
     * @param $article \Shopware\Models\Article\Article
     * @param $tax \Shopware\Models\Tax\Tax
     * @return array
     */
    protected function preparePricesAssociatedData($prices, $article, $tax)
    {
        foreach($prices as $key => &$priceData) {
            //load the customer group of the price definition
            $customerGroup = $this->getCustomerGroupRepository()->findOneBy(array('key' => $priceData['customerGroupKey']));

            //if no customer group found, remove price and continue
            if (!$customerGroup instanceof \Shopware\Models\Customer\Group) {
                unset($prices[$key]);
                continue;
            }

            $priceData['to'] = intval($priceData['to']);

            //if the "to" value isn't numeric, set the place holder "beliebig"
            if ($priceData['to'] <= 0) {
                $priceData['to'] = 'beliebig';
            }

            if ($customerGroup->getTaxInput()) {
                $priceData['price'] = $priceData['price'] / (100 + $tax->getTax()) * 100;
                $priceData['pseudoPrice'] = $priceData['pseudoPrice'] / (100 + $tax->getTax()) * 100;
            }

            //resolve the oneToMany association of ExtJs to an oneToOne association for doctrine.
            $priceData['attribute'] = $priceData['attribute'][0];
            $priceData['customerGroup'] = $customerGroup;
            $priceData['article'] = $article;
            $priceData['articleDetail'] = $article->getMainDetail();
        }

        return $prices;
    }

    /**
     * Prepares the link data of the article.
     * @param $data
     * @return array
     */
    protected function prepareLinkAssociatedData($data)
    {
        foreach($data['links'] as &$linkData) {
            $linkData['link'] = trim($linkData['link']);
            $linkData['attribute'] = $linkData['attribute'][0];
            // map the boolean ExtJS link target to the string format which used in the database
            $linkData['target'] = ($linkData['target'] === true) ? "_blank" : "_parent";
        }
        return $data;
    }

    /**
     * Prepares the download data of the article.
     * @param $data
     * @return array
     */
    protected function prepareDownloadAssociatedData($data)
    {
        foreach($data['downloads'] as &$downloadData) {
            $downloadData['attribute'] = $downloadData['attribute'][0];
        }
        return $data;
    }

    /**
     * Returns a list of all article detail templates as array.
     * @return array
     */
    protected function getTemplates()
    {
        $config = Shopware()->Config()->detailTemplates;
        $data = array();
        foreach (explode(';', $config) as $path) {
            list($id, $name) = explode(':', $path);
            $data[] = array('id' => $id, 'name' => $name);
        }
        return $data;
    }

    /**
     * Internal helper function which returns default data for a new article.
     * @return array
     */
    protected function getNewArticleData()
    {
        $prefix = Shopware()->Config()->backendAutoOrderNumberPrefix;

        $sql = "SELECT number FROM s_order_number WHERE name = 'articleordernumber'";
        $number = Shopware()->Db()->fetchOne($sql);

        do {
            $number++;

            $sql = "SELECT id FROM s_articles_details WHERE ordernumber LIKE ?";
            $hit = Shopware()->Db()->fetchOne($sql, $prefix . $number);
        } while ($hit);

        return array(
            'number'     => $prefix . $number,
            'autoNumber' => $number
        );
    }

    /**
     * Event listener function of the article store of the backend module.
     * @return mixed
     */
    public function deleteAction()
    {
        try {
            if (!$this->Request()->has('id')) {
                return;
            }
            $id = (int) $this->Request()->getParam('id');
            $article = $this->getRepository()->find($id);
            if (!$article instanceof \Shopware\Models\Article\Article) {
                return;
            }
            $this->removePrices($article->getId());
            $this->removeArticleEsd($article->getId());
            $this->removeAttributes($article->getId());
            $this->removeArticleDetails($article);

            Shopware()->Models()->remove($article);
            Shopware()->Models()->flush();
            $this->View()->assign(array(
                'data' => $this->Request()->getParams(),
                'success' => true
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Internal helper function to remove all article prices quickly.
     * @param $articleId
     */
    protected function removePrices($articleId)
    {
        $query = $this->getRepository()->getRemovePricesQuery($articleId);
        $query->execute();
    }

    /**
     * Internal helper function to remove the article attributes quickly.
     * @param $articleId
     */
    protected function removeAttributes($articleId)
    {
        $query = $this->getRepository()->getRemoveAttributesQuery($articleId);
        $query->execute();
    }

    /**
     * Internal helper function to remove the detail esd configuration quickly.
     * @param $articleId
     */
    protected function removeArticleEsd($articleId)
    {
        $query = $this->getRepository()->getRemoveESDQuery($articleId);
        $query->execute();
    }

    /**
     * @param $article \Shopware\Models\Article\Article
     */
    protected function removeArticleDetails($article)
    {
        $sql= "SELECT id FROM s_articles_details WHERE articleID = ? AND kind != 1";
        $details = Shopware()->Db()->fetchAll($sql, array($article->getId()));

        foreach($details as $detail) {
            $query = $this->getRepository()->getRemoveImageQuery($detail['id']);
            $query->execute();

            $sql= "DELETE FROM s_article_configurator_option_relations WHERE article_id = ?";
            Shopware()->Db()->query($sql, array($detail['id']));

            $query = $this->getRepository()->getRemoveDetailQuery($detail['id']);
            $query->execute();
        }
    }

    /**
     * Event listener function of the configurator group model of the article backend module.
     * Fired when the user want to remove a configurator group.
     * The function requires a passed id to load the shopware model an remove it over the model manager.
     */
    public function deleteConfiguratorGroupAction()
    {
        try {
            if (!$this->Request()->has('id')) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => 'No valid id passed'
                ));
            }
            $model = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Group', (int) $this->Request()->getParam('id'));
            if (!$model instanceof \Shopware\Models\Article\Configurator\Group) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => 'No valid id passed'
                ));
            }
            $builder = Shopware()->Models()->createQueryBuilder();
            $boundedArticles = $builder->select(array('articles'))
                                       ->from('Shopware\Models\Article\Detail', 'articles')
                                       ->innerJoin('articles.configuratorOptions', 'options')
                                       ->where('options.groupId = ?1')
                                       ->setParameter(1, (int) $this->Request()->getParam('id'))
                                       ->getQuery()
                                       ->getArrayResult();

            if (count($boundedArticles) > 0) {
                $articles = array();
                foreach($boundedArticles as $article) {
                    $articles[] = $article['number'] . ' - ' . $article['additionalText'];
                }

                $this->View()->assign(array(
                    'success' => false,
                    'articles' => $articles,
                    'message' => 'Articles bounded on this group!'
                ));
                return;
            }

            Shopware()->Models()->remove($model);
            Shopware()->Models()->flush();
            $this->View()->assign(array(
                'success' => true
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Event listener function of the configurator listing. Fired when the user
     * selects one or many rows in the configurator listing and clicks the delete button.
     */
    public function deleteDetailAction()
    {
        $details = $this->Request()->getParam('details', array(array('id' => (int) $this->Request()->getParam('id'))));

        try {
            $article = null;
            foreach($details as $detail) {
                if (empty($detail['id'])) {
                    continue;
                }
                /**@var $model \Shopware\Models\Article\Detail*/
                $model = Shopware()->Models()->find('Shopware\Models\Article\Detail', $detail['id']);
                if (!$model instanceof \Shopware\Models\Article\Detail) {
                    continue;
                }
                if ($article === null) {
                    $article = $model->getArticle();
                }
                if ($model->getId() !== $article->getMainDetail()->getId()) {
                    Shopware()->Models()->remove($model);
                }
            }
            Shopware()->Models()->flush();

            $this->View()->assign(array(
                'success' => true
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'error' => $e->getMessage()
            ));
        }
    }

    /**
     * Event listener function of the configurator group model of the article backend module.
     * Fired when the user want to remove a configurator group.
     * The function requires a passed id to load the shopware model an remove it over the model manager.
     */
    public function deleteConfiguratorOptionAction()
    {
        try {
            $id = (int) $this->Request()->getParam('id');

            if (empty($id) || $id < 0) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => 'No valid id passed'
                ));
            }
            $model = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Option', $id);
            if (!$model instanceof \Shopware\Models\Article\Configurator\Option) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => 'No valid id passed'
                ));
            }
            $builder = Shopware()->Models()->createQueryBuilder();
            $boundedArticles = $builder->select(array('articles'))
                                       ->from('Shopware\Models\Article\Detail', 'articles')
                                       ->innerJoin('articles.configuratorOptions', 'options')
                                       ->where('options.id = ?1')
                                       ->setParameter(1, $id)
                                       ->getQuery()
                                       ->getArrayResult();

            if (count($boundedArticles) > 0) {
                $articles = array();
                foreach($boundedArticles as $article) {
                    $articles[] = $article['number'] . ' - ' . $article['additionalText'];
                }

                $this->View()->assign(array(
                    'success' => false,
                    'articles' => $articles,
                    'message' => 'Articles bounded on this option!'
                ));
                return;
            }

            Shopware()->Models()->remove($model);
            Shopware()->Models()->flush();
            $this->View()->assign(array(
                'success' => true
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Increase the number of the s_order_number
     * @param $autoNumber
     * @param $number
     * @return void
     * @internal param $data
     * @internal param $mainDetail
     */
    protected function increaseAutoNumber($autoNumber, $number)
    {
        if (strlen($number) > 2) {
            $number = substr($number, strlen(Shopware()->Config()->backendAutoOrderNumberPrefix));
        }
        if ($number == $autoNumber) {
            $sql= "UPDATE s_order_number SET number = ? WHERE name = 'articleordernumber'";
            Shopware()->Db()->query($sql, array($autoNumber));
        }
    }


    /**
     * Event listener function of the article backend module.
     * Will be fired when the user changes to the ESD-Tab.
     */
    public function getEsdAction()
    {
        if ($this->Request()->getParam('filterCandidates', false)) {
            $articleId = $this->Request()->getParam('articleId');

            $builder = $this->getManager()->createQueryBuilder();

            $builder->select(array(
                        'articleDetail.id as id',
                        'article.name as name',
                        'articleDetail.id as articleDetailId',
                        'articleDetail.additionalText as additionalText',
                        'article.id as articleId',
                    ))
                    ->from('Shopware\Models\Article\Detail', 'articleDetail')
                    ->leftJoin('articleDetail.esd', 'esd')
                    ->leftJoin('articleDetail.article', 'article')
                    ->where('articleDetail.articleId = :articleId')
                    ->andWhere('esd.id IS NULL')
                    ->setParameter('articleId', $articleId);

            $query = $builder->getQuery();
            $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

            //returns the total count of the query
            $totalResult = $paginator->count();

            //returns the customer data
            $result = $paginator->getIterator()->getArrayCopy();

            foreach($result as &$item) {
                if (!empty($item['additionalText'])) {
                    $item['name'] .= ' - ' . $item['additionalText'];
                }
            }

            $this->View()->assign(array(
                'data' =>  $result,
                'total' => $totalResult,
                'success' => true
            ));

            return;
        }

        $articleId = $this->Request()->getParam('articleId');

        $filter = $this->Request()->getParam('filter');
        $sort   = $this->Request()->getParam('sort');
        $start = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 20);

        $query = $this->getRepository()->getEsdByArticleQuery($articleId, $filter, $limit, $start, $sort);
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the customer data
        $result = $paginator->getIterator()->getArrayCopy();

        $this->View()->assign(array(
            'data' => $result,
            'total' => $totalResult,
            'success' => true
        ));
    }

    /**
     * Event listener function of the article backend module.
     * Will be fired when the user clicks the edit esd-button.
     */
    public function getSerialsAction()
    {
        $esdId = $this->Request()->getParam('esdId');

        $filter = $this->Request()->getParam('filter');
        $sort   = $this->Request()->getParam('sort');
        $start = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 20);

        $query = $this->getRepository()->getSerialsByEsdQuery($esdId, $filter, $start, $limit, $sort);
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the customer data
        $result = $paginator->getIterator()->getArrayCopy();

        $this->View()->assign(array(
            'data' =>  $result,
            'total' => $totalResult,
            'success' => true
        ));
    }

    public function createEsdAction()
    {
        try {
            $articleDetailId = $this->Request()->getPost('articleDetailId');

            /** @var $articleDetail \Shopware\Models\Article\Detail */
            $articleDetail = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail')->find($articleDetailId);
            if (!$articleDetail) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => sprintf('ArticleDetail by id %s not found', $articleDetailId)
                ));
                return;
            }

            $esd = new \Shopware\Models\Article\Esd();
            $esd->setArticleDetail($articleDetail);

            $this->getManager()->persist($esd);
            $this->getManager()->flush();

            $this->View()->assign(array(
                'success' => true
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Event listener function of the article backend module.
     * Will be fired when the user saves ESD
     */
    public function saveEsdAction()
    {
        try {
            $esdId = $this->Request()->getPost('id');

            /** @var $esd \Shopware\Models\Article\Esd */
            $esd = Shopware()->Models()->getRepository('Shopware\Models\Article\Esd')->find($esdId);
            if (!$esd) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => sprintf('ESD by id %s not found', $esdId)
                ));
                return;
            }

            $freeSerialsCount = $this->getFreeSerialCount($esdId);
            $articleDetail = $esd->getArticleDetail();
            $articleDetail->setInStock($freeSerialsCount);

            $esd->fromArray($this->Request()->getPost());
            $this->getManager()->flush();

            $this->View()->assign(array(
                'data' =>  $this->Request()->getPost(),
                'success' => true
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Event listener function of the article backend module.
     * Will be fired when the user deletes ESD
     */
    public function deleteEsdAction()
    {
        $details = $this->Request()->getParam('details', array(array('id' => $this->Request()->getParam('id'))));
        try {
            foreach($details as $detail) {
                if (empty($detail['id'])) {
                    continue;
                }

                $model = Shopware()->Models()->find('Shopware\Models\Article\Esd', $detail['id']);
                if (!$model) {
                    continue;
                }
                Shopware()->Models()->remove($model);
            }

            Shopware()->Models()->flush();
            $this->View()->assign(array(
                'success' => true
            ));
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'error' => $e->getMessage()
            ));
        }
    }

    /**
     * Event listener function of the article backend module.
     * Will be fired when the user deletes serials
     */
    public function deleteSerialsAction()
    {
        $esdId = $this->Request()->getParam('esdId');

        $details = $this->Request()->getParam('details', array(array('id' => $this->Request()->getParam('id'))));

        try {
            foreach($details as $detail) {
                if (empty($detail['id'])) {
                    continue;
                }

                $model = Shopware()->Models()->find('Shopware\Models\Article\EsdSerial', $detail['id']);
                if (!$model) {
                    continue;
                }
                Shopware()->Models()->remove($model);
            }

            Shopware()->Models()->flush();
            $this->View()->assign(array(
                'success' => true
            ));

            // Update stock
            /** @var $esd \Shopware\Models\Article\Esd */
            $esd = Shopware()->Models()->getRepository('Shopware\Models\Article\Esd')->find($esdId);
            $freeSerialsCount = $this->getFreeSerialCount($esdId);
            $articleDetail = $esd->getArticleDetail();
            $articleDetail->setInStock($freeSerialsCount);
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'error' => $e->getMessage()
            ));
        }
    }

    /**
     * Event listener function of the article backend module.
     * Deletes unused serialsnumbers
     */
    public function deleteUnusedSerialsAction()
    {
        $esdId = $this->Request()->getParam('esdId');

        $query = $this->getRepository()->getUnusedSerialsByEsdQuery($esdId);
        $serials = $query->execute();
        $totalCount = count($serials);

        foreach ($serials as $serial) {
            $this->getManager()->remove($serial);
        }

        $this->getManager()->flush();

        $this->View()->assign(array(
            'total'   => $totalCount,
            'success' => true
        ));

        // Update stock
        /** @var $esd \Shopware\Models\Article\Esd */
        $esd = Shopware()->Models()->getRepository('Shopware\Models\Article\Esd')->find($esdId);
        $freeSerialsCount = $this->getFreeSerialCount($esdId);
        $articleDetail = $esd->getArticleDetail();
        $articleDetail->setInStock($freeSerialsCount);

        $this->getManager()->flush();
    }

    /**
     * Return number of free serials for given esdId
     *
     * @param int $esdId
     * @return int
     */
    public function getFreeSerialCount($esdId)
    {
        $query = $this->getRepository()->getFreeSerialsCountByEsdQuery($esdId);
        $result = $query->getSingleScalarResult();

        return $result;
    }

    /**
     * Event listener function of the article backend module.
     * Creates new serial numbers
     */
    public function saveSerialsAction()
    {
        try {
            $esdId = $this->Request()->getParam('esdId');

            /** @var $esd \Shopware\Models\Article\Esd */
            $esd = Shopware()->Models()->getRepository('Shopware\Models\Article\Esd')->find($esdId);

            if (!$esd) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => sprintf('ESD by id %s not found', $esdId)
                ));
                return;
            }

            $serials = $this->Request()->getParam('serials');

            // split string at newlines (WIN, Linux, OSX)
            $serials = preg_split('/$\R?^/m', $serials);

            // trim every serialnumber
            array_walk($serials, 'trim');

            // remove empty serialnumbers
            $serials = array_filter($serials);

            // remove duplicates
            $serials = array_unique($serials);

            $newSerials = 0;

            foreach ($serials as $serialnumber) {
                $serialnumber = trim($serialnumber);
                $serial = Shopware()->Models()->getRepository('Shopware\Models\Article\EsdSerial')->findOneBy(array('serialnumber' => $serialnumber));
                if ($serial) {
                    continue;
                }

                $serial = new \Shopware\Models\Article\EsdSerial();
                $serial->setSerialnumber($serialnumber);
                $serial->setEsd($esd);
                $this->getManager()->persist($serial);
                $newSerials++;
            }
            $this->getManager()->flush();

            // Update stock
            $freeSerialsCount = $this->getFreeSerialCount($esdId);
            $articleDetail = $esd->getArticleDetail();
            $articleDetail->setInStock($freeSerialsCount);

            $this->getManager()->flush();

            $this->View()->assign(array(
                'success' => true,
                'total' => $newSerials
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Event listener function of the article backend module.
     * Returns list of ESD-Files
     */
    public function getEsdFilesAction()
    {
        $filePath = Shopware()->DocPath('files_' . Shopware()->Config()->get('sESDKEY'));

        $result = array();
        foreach (new DirectoryIterator($filePath) as $file) {
            if ($file->isDot() || strpos($file->getFilename(), '.') === 0) {
                continue;
            }
            $result[] = array(
                'filename' => $file->getFilename()
            );
        }

        $this->View()->assign(array(
            'data' =>  $result,
            'total' =>  count($result),
            'success' => true
        ));
    }

    /**
     * Event listener function of the article backend module.
     * Uploads ESD-File
     */
    public function uploadEsdFileAction()
    {
        $destinationDir = Shopware()->DocPath('files_' . Shopware()->Config()->get('sESDKEY'));

        try {
            $fileBag = new \Symfony\Component\HttpFoundation\FileBag($_FILES);
            /** @var $file Symfony\Component\HttpFoundation\File\UploadedFile */
            $file = $fileBag->get('fileId');
            $file->move($destinationDir, $file->getClientOriginalName());
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
            return;
        }
        if ($file === null) {
            $this->View()->assign(array('success' => false));
            return;
        }

        $this->View()->assign(array('success' => true));
    }

    /**
     * Event listener function of the article backend module.
     * Downloads ESD-File
     */
    public function getEsdDownloadAction()
    {
        $filename = $this->Request()->getParam('filename');
        $file = 'files/' . Shopware()->Config()->get('sESDKEY') . '/' . $filename;

        if (!file_exists(Shopware()->OldPath() . $file)) {
            $this->View()->assign(array(
                'message' => 'File not found',
                'success' => false
            ));

            return;
        }
        $this->redirect($file);
    }

    /**
     * Event listener function of the article backend module.
     * Returns statistical data
     */
    public function getChartData()
    {
        $articleId = $this->Request()->getParam('articleId');
        $format = 'month';

        if ($format == 'month') {
            $dateFormat = '%Y%m';
            $limit = 12;
        } else {
            $dateFormat = '%Y%m%d';
            $limit = 32;
        }

        $sql = sprintf("
            SELECT
                SUM(price*quantity) AS revenue,
                SUM(quantity) AS orders,
                DATE_FORMAT(ordertime, '%s') as groupdate,
                WEEK(ordertime) as week,
                MONTH(ordertime) as month,
                ordertime as date
            FROM s_order_details, s_order
            WHERE articleID = ?
                AND s_order.id = s_order_details.orderID
                AND s_order.status != 4
                AND s_order.status != -1
            GROUP BY groupdate
            ORDER BY groupdate ASC
            LIMIT %d
        ", $dateFormat, $limit);

        $stmt = Shopware()->Db()->query($sql, $articleId);
        $result = $stmt->fetchAll();

        $this->View()->assign(array(
            'data' =>  $result,
            'total' =>  count($result),
            'success' => true
        ));
    }

    /**
     * The "regenerateVariantOrderNumbersAction" allows the user to recreate
     * the article variant order number with an own number syntax.
     * Called from the article backend module.
     */
    public function regenerateVariantOrderNumbersAction()
    {
        try {
            $data = $this->Request()->getParams();
            $articleId = $data['articleId'];
            $syntax = $data['syntax'];
            if (!$articleId > 0 || strlen($syntax) === 0) {
                return;
            }

            $article = $this->getRepository()
                            ->getArticleWithVariantsAndOptionsQuery($articleId)
                            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

            $abortId = $article->getMainDetail()->getId();
            $commands = $this->prepareNumberSyntax($syntax);
            $details = $article->getDetails();

            $counter = 1;
            /** @var $detail \Shopware\Models\Article\Detail */
            foreach ($details as $detail) {
                if ($detail->getId() === $abortId) {
                    continue;
                }
                $number = $this->interpretNumberSyntax($article, $detail, $commands, $counter);
                $counter++;
                if (strlen($number) === 0) {
                    continue;
                }
                $detail->setNumber($number);
                Shopware()->Models()->persist($detail);
            }
            Shopware()->Models()->flush();
            $this->View()->assign(array(
                'success' => true
            ));
        }
        catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Start function for number generation. Iterates the different commands,
     * resolves the cursor or counter and starts the recursive
     *
     * @param $article
     * @param $detail
     * @param $commands
     * @param $counter
     * @return string
     */
    protected function interpretNumberSyntax($article, $detail, $commands, $counter)
    {
        $name = array();
        foreach($commands as $command) {
            //if the command isn't equals "n" we have to execute commands
            if ($command !== 'n') {
                //first we have to resolve the cursor object which used for the first command
                //the cursor object is set over the "prepareNumberSyntax" function.
                if ($command['cursor'] === 'detail') {
                    $cursor = $detail;
                } else {
                    $cursor = $article;
                }
                //call the recursive interpreter to resolve all commands
                $name[] = $this->recursiveInterpreter($cursor, 0, $command['commands']);
            } else {
                $name[] = $counter;
            }
        }
        //return all command results, concat with a dot
        return implode('.', $name);
    }

    /**
     * This function executes the different commands for the number regeneration function.
     * First the function executes the current command on the passed cursor object.
     * If the result is traversable
     *
     * @param $cursor
     * @param $index
     * @param $commands
     * @return string
     */
    protected function recursiveInterpreter($cursor, $index, $commands)
    {
        if (!is_object($cursor)) {
            return '';
        }

        //first we execute the current command on the cursor object
        $result = $cursor->$commands[$index]();
        //now we increment the command index
        $index++;

        //if the result of the current command on the cursor is an array
        if ($result instanceof \Traversable) {
            //we have to execute the following command on each array element.
            $results = array();
            foreach($result as $object) {
                $results[] = $this->recursiveInterpreter($object, $index, $commands);
            }
            return implode('.', $results);

        //if the result of the current command on the cursor is an object
        } elseif (is_object($result)) {
            //we have to execute the next command on the result
            return $this->recursiveInterpreter($result, $index, $commands);

        //otherwise we can return directly.
        } else {
            return $result;
        }

    }

    /**
     * Prepares the passed number syntax. Executes a regular expression
     * to get all syntax commands and maps this commands to the rout
     * object.
     * @param $syntax
     * @return array
     */
    protected function prepareNumberSyntax($syntax)
    {
        preg_match_all('#\{(.*?)\}#msi', $syntax, $result);
        $syntax = $result[1];

        $properties = array();
        foreach($syntax as $path) {
            if ($path !== 'n') {
                $properties[] = $this->getCommandMapping($path);
            } else {
                $properties[] = $path;
            }
        }
        return $properties;
    }

    /**
     * Internal helper function which helps the get the cursor object for the passed syntax command.
     */
    protected function getCommandMapping($syntax)
    {
        //we have to explode the current command to resolve the multiple properties.
        $paths = explode('.', $syntax);

        //we have to map the different properties to define the start cursor object.
        switch($paths[0]) {
            //options are only available for the different article variants
            case "options":
                $cursor = 'detail';
                $paths[0] = "configuratorOptions";
                break;
            //all other commands will rout to the article
            default:
                $cursor = 'article';
        }

        $commands = array();

        //now we convert the property names to the getter functions.
        foreach($paths as $path) {
            $commands[] = 'get' . ucfirst($path);
        }

        return array(
            'cursor' => $cursor,
            'commands' => $commands
        );
    }

    /**
     * Event listener function of the article backend module.
     * Returns statistical data
     */
    public function getStatisticAction()
    {
        $articleId = $this->Request()->getParam('articleId');

        if ($this->Request()->getParam('chart', false)) {
            return $this->getChartData();
        }

        $startDate = $this->Request()->getParam('fromDate', date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y"))));
        $endDate   = $this->Request()->getParam('toDate', date("Y-m-d"));

        $sql = "
            SELECT
            SUM(price*quantity) AS revenue,
            SUM(quantity) AS orders,
            MONTH(ordertime) as month,
            DATE_FORMAT(ordertime, '%Y-%m-%d') as date
            FROM s_order_details, s_order
            WHERE articleID = :articleId
            AND s_order.id = s_order_details.orderID
            AND s_order.status != 4
            AND s_order.status != -1
            AND TO_DAYS(ordertime) <= TO_DAYS(:endDate)
            AND TO_DAYS(ordertime) >= TO_DAYS(:startDate)
            GROUP BY TO_DAYS(ordertime)
            ORDER BY ordertime DESC
        ";

        $stmt = Shopware()->Db()->query($sql, array(
            'endDate'   => $endDate,
            'startDate' => $startDate,
            'articleId' => $articleId,
        ));
        $result = $stmt->fetchAll();

        $this->View()->assign(array(
            'data' => $result,
            'total' => count($result),
            'success' => true
        ));
    }

    /**
     * Remote validator for the article order number field.
     * The passed value must be set and the number must be unique
     *
     * @return string|void returns the string "true" if valid, nothing otherwise
     */
    public function validateNumberAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        $exist = $this->getRepository()
                      ->getValidateNumberQuery($this->Request()->value, $this->Request()->param)
                      ->getArrayResult();

        if (empty($exist) && strlen($this->Request()->value) > 0) {
            echo 'true';
        } else {
            return;
        }
    }

    /**
     * Event listener function of the backend module. Fired when the user select a shop in the shop combo in the option
     * panel of the sidebar and clicks on the "preview" button to display the article details in the store front.
     */
    public function previewDetailAction()
    {
   		$shopId = (int)$this->Request()->getParam('shopId');
   		$articleId = (int)$this->Request()->getParam('articleId');

        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repository->getActiveById($shopId);
        $shop->registerResources(Shopware()->Bootstrap());

   		Shopware()->Session()->Admin = true;

   		$url = $this->Front()->Router()->assemble(array(
           'module' => 'frontend',
           'controller' => 'detail',
           'sArticle' => $articleId,
           'appendSession' => true
        ));

   		$this->redirect($url);
   	}

    /**
     * Internal helper function to get the field names of the passed violation array.
     * @param $violations \Symfony\Component\Validator\ConstraintViolationList
     * @return string
     */
    protected function getViolationFields($violations)
    {
        $fields = array();
        /**@var $violation Symfony\Component\Validator\ConstraintViolation*/
        foreach($violations as $violation) {
            $fields[] = $violation->getPropertyPath();
        }
        return $fields;
    }
}
