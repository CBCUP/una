<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 *
 * @defgroup    Forum Forum
 * @ingroup     TridentModules
 *
 * @{
 */

class BxForumSearchResult extends BxBaseModTextSearchResult
{
    function __construct($sMode = '', $aParams = array())
    {
        parent::__construct($sMode, $aParams);

        $this->aCurrent = array(
            'name' => 'bx_forum',
            'module_name' => 'bx_forum',
            'object_metatags' => 'bx_forum',
            'title' => _t('_bx_forum_page_title_browse'),
            'table' => 'bx_forum_discussions',
            'ownFields' => array('id', 'title', 'text', 'author', 'added', 'comments', 'lr_profile_id'),
            'searchFields' => array('title', 'text'),
            'restriction' => array(
                'author' => array('value' => '', 'field' => 'author', 'operator' => '='),
        		'status' => array('value' => 'active', 'field' => 'status', 'operator' => '='),
            ),
            'paginate' => array('perPage' => getParam('bx_forum_per_page_browse'), 'start' => 0),
            'sorting' => 'last',
            'rss' => array(
                'title' => '',
                'link' => '',
                'image' => '',
                'profile' => 0,
                'fields' => array (
                    'Guid' => 'link',
                    'Link' => 'link',
                    'Title' => 'title',
                    'DateTimeUTS' => 'added',
                    'Desc' => 'text',
                ),
            ),
            'ident' => 'id',
        );

        $this->sFilterName = 'bx_forum_filter';
        $this->oModule = $this->getMain();

        $oProfileAuthor = null;

        $CNF = &$this->oModule->_oConfig->CNF;

        switch ($sMode) {
            case 'author':
                $oProfileAuthor = BxDolProfile::getInstance((int)$aParams['author']);
                if (!$oProfileAuthor) {
                    $this->isError = true;
                    break;
                }

                $this->aCurrent['restriction']['author']['value'] = $oProfileAuthor->id();

                $this->sBrowseUrl = 'page.php?i=' . $CNF['URI_AUTHOR_ENTRIES'] . '&profile_id={profile_id}';
                $this->aCurrent['title'] = _t('_bx_forum_page_title_browse_by_author');
                $this->aCurrent['rss']['link'] = 'modules/?r=forum/rss/' . $sMode . '/' . $oProfileAuthor->id();
                break;

			case 'new':
            case 'public':
                $this->sBrowseUrl = BxDolPermalinks::getInstance()->permalink($CNF['URL_NEW']);
                $this->aCurrent['title'] = _t('_bx_forum_page_title_browse_new');
                $this->aCurrent['rss']['link'] = 'modules/?r=forum/rss/' . $sMode;
                break;

			case 'latest':
                $this->sBrowseUrl = BxDolPermalinks::getInstance()->permalink($CNF['URL_TOP']);
                $this->aCurrent['title'] = _t('_bx_forum_page_title_browse_latest');
                $this->aCurrent['rss']['link'] = 'modules/?r=forum/rss/' . $sMode;
                $this->aCurrent['sorting'] = 'latest';
                break;

            case 'top':
                $this->sBrowseUrl = BxDolPermalinks::getInstance()->permalink($CNF['URL_TOP']);
                $this->aCurrent['title'] = _t('_bx_forum_page_title_browse_top');
                $this->aCurrent['rss']['link'] = 'modules/?r=forum/rss/' . $sMode;
                $this->aCurrent['sorting'] = 'top';
                break;

			case 'popular':
                $this->sBrowseUrl = BxDolPermalinks::getInstance()->permalink($CNF['URL_POPULAR']);
                $this->aCurrent['title'] = _t('_bx_forum_page_title_browse_popular');
                $this->aCurrent['rss']['link'] = 'modules/?r=forum/rss/' . $sMode;
                $this->aCurrent['sorting'] = 'popular';
                break;

            case 'updated':
                $this->sBrowseUrl = BxDolPermalinks::getInstance()->permalink($CNF['URL_HOME']);
                $this->aCurrent['title'] = _t('_bx_forum_page_title_browse_updated');
                $this->aCurrent['rss']['link'] = 'modules/?r=forum/rss/' . $sMode;
                $this->aCurrent['sorting'] = 'updated';
                break;

            case '': // search results
                $this->sBrowseUrl = BX_DOL_SEARCH_KEYWORD_PAGE;
                $this->aCurrent['title'] = _t('_bx_forum');
                $this->aCurrent['paginate']['perPage'] = 3;
                unset($this->aCurrent['rss']);
                break;

            default:
                $sMode = '';
                $this->isError = true;
        }

        $this->processReplaceableMarkers($oProfileAuthor);

        $this->addConditionsForPrivateContent($CNF, $oProfileAuthor);
    }

    function getAlterOrder()
    {
        $aSql = array();
        switch ($this->aCurrent['sorting']) {
            case 'last':
                $aSql['order'] = ' ORDER BY `bx_forum_discussions`.`added` DESC';
                break;
			case 'updated':
                $aSql['order'] = ' ORDER BY `bx_forum_discussions`.`changed` DESC';
                break;
			case 'latest':
                $aSql['order'] = ' ORDER BY `bx_forum_discussions`.`lr_timestamp` DESC';
                break;
			case 'top':
                $aSql['order'] = ' ORDER BY `bx_forum_discussions`.`comments` DESC';
                break;
            case 'popular':
                $aSql['order'] = ' ORDER BY `bx_forum_discussions`.`views` DESC';
                break;
        }
        return $aSql;
    }
}

/** @} */
