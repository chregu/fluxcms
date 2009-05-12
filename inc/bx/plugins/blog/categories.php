<?php
/**
 * handles the categores
 *
 * @todo: documentation
 * */
class bx_plugins_blog_categories {
    static function getContentById($path, $id, $params, $parent = null, $tablePrefix = "") {
        $parts = bx_collections::getCollectionAndFileParts($path, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index", "html");
        $p = $p['plugin'];
        $colluri = $parts['coll']->uri;
        $blogid = $p->getParameter($colluri, "blogid");
        $bloglanguage = $GLOBALS['POOL']->config->blogShowOnlyOneLanguage;
        $lang = $GLOBALS['POOL']->config->getOutputLanguage();

        if (!$blogid) {
            $blogid = 1;
        }
        if (isset($params[1]) && $params[1] == 'count') {
            $perm = bx_permm::getInstance();
            if ($perm->isLoggedIn()) {
                if ($id == "_all/index") {
                    $overviewPerm = 7;
                } else {
                    $overviewPerm = 3;
                }
            } else {
                $overviewPerm = 1;
            }
            $query = "select id,parentid from " . $tablePrefix . "blogcategories as blogcategories where status = 1 and blog_id = " . $blogid;

            $res = $GLOBALS['POOL']->db->query($query);
            if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
            }

            $allcats = $res->fetchAll($query, true);
            $query = "select blogcategories.id,count(*),
           blogcategories.parentid
           from " . $tablePrefix . "blogcategories as blogcategories
           left join " . $tablePrefix . "blogposts2categories on " . $tablePrefix . "blogposts2categories.blogcategories_id = blogcategories.id
           left join " . $tablePrefix . "blogposts  on " . $tablePrefix . "blogposts.id = " . $tablePrefix . "blogposts2categories.blogposts_id
           where  " . $tablePrefix . "blogposts.id > 0 and " . $tablePrefix . "blogposts.post_status & " . $overviewPerm;
            $query .= " and  blogcategories.blog_id = " . $blogid;
            if ($bloglanguage == 'true') {
                $q .= ' and (blogposts.post_lang = "' . $lang . '" or blogposts.post_lang = "")';
            }

            $query .= " group by " . $tablePrefix . "blogposts2categories.blogcategories_id order by l desc";
            $res = $GLOBALS['POOL']->db->query($query);

            if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
            }
            $catCount = $res->fetchAll($query, true);
            foreach ($catCount as $key => $value) {
                if (!isset($catCount[$value[1]])) {
                    $catCount[$value[1]] = array(
                            $value[0],
                            $allcats[$value[1]][1]
                    );
                } else if ($value[1] > 0 && isset($catCount[$value[1]])) {
                    $catCount[$value[1]][0] += $value[0];
                }
            }

        } else {
            $catCount = false;
        }
        if (!$rows = $GLOBALS['POOL']->cache->get("plugins_blog_categories_tree_" . $blogid)) {
            $tree = bx_plugins_blog::getTreeInstance($tablePrefix);
            $data = array(
                    "name",
                    "uri",
                    "fulluri",
                    "status",
                    "id"
            );
            $query = $tree->children_query_byname(array(
                    "blog_id" => $blogid,
                    "parentid" => 0
            ), $data, True);
            $res = $GLOBALS['POOL']->db->query($query);
            if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
            }
            $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
            $GLOBALS['POOL']->cache->set("plugins_blog_categories_tree_" . $blogid, $rows, null, "table_blogcategories");
        }

        if (isset($params[0])) {
            $lastslash = strrpos($params[0], "/");
            $cat = substr($params[0], 0, $lastslash);
        } else {
            $cat = "";
        }
        $oldlevel = 1;
        $dom = new DomDocument();
        $parent = $dom;
        foreach ($rows as $row) {

            // Skip categories containing no-, or only posts without sufficient perms.
            if (!isset($catCount[$row['id']]) || $catCount[$row['id']][0] <= 0) {
                continue;
            }

            if ($row['status'] != 1) {
                continue;
            }
            if ($row['level'] == 1) {
                $roottitle = $row['name'];
            }
            if ($oldlevel < $row['level']) {
                if (isset($coll) && $coll) {
                    $parent = $coll->appendChild($dom->createElement("items"));
                } else {
                    // if we're here, we have a problem, break...
                    break;
                }
            }
            if ($oldlevel > $row['level']) {
                $diff = $oldlevel - $row['level'];
                for ($i = 0; $i < $diff; $i++) {
                    $parent = $parent->parentNode->parentNode;
                }
            }

            $coll = $dom->createElement("collection");
            $coll->setAttribute("level", $row['level']);
            if ($catCount) {
                if (isset($catCount[$row['id']]) && $catCount[$row['id']][0] > 0) {
                    $coll->setAttribute("count", $catCount[$row['id']][0]);
                } else {
                    $coll->setAttribute("count", 0);
                }

            }

            if (strpos($cat, $row['fulluri']) === 0 || ($cat === "" && $row['fulluri'] === "root")) {
                $coll->setAttribute("selected", "selected");
            } else {
                $coll->setAttribute("selected", "all");
            }
            $titel = $dom->createElement("title", htmlspecialchars(html_entity_decode($row['name'], ENT_NOQUOTES, 'UTF-8')));
            $uri = $dom->createElement("uri", htmlspecialchars(BX_WEBROOT_W . $path . $row['fulluri'] . "/"));
            $do = $dom->createElement("display-order", htmlspecialchars($row['l']));

            $coll->appendChild($titel);
            $coll->appendChild($uri);
            $coll->appendChild($do);
            $parent->appendChild($coll);
            $oldlevel = $row['level'];
        }
        $coll = $dom->createElement("collection");
        if ($cat === "") {
            $coll->setAttribute("selected", "selected");
        } else {
            $coll->setAttribute("selected", "all");
        }
        if (isset($roottitle)) {
            $titel = $dom->createElement("title", htmlspecialchars($roottitle));
        } else {
            $titel = $dom->createElement("title", "");
        }
        $uri = $dom->createElement("uri", htmlspecialchars($path));
        $do = $dom->createElement("display-order", "0.1");

        $coll->appendChild($titel);
        $coll->appendChild($uri);
        $coll->appendChild($do);
        for ($i = 2; $i < $oldlevel; $i++) {
            $parent = $parent->parentNode->parentNode;
        }

        $parent->insertBefore($coll, $parent->firstChild);
        return $dom;
    }

}
?>
