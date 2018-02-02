
plugin {
    tx_searchcore {
        settings {
            connections {
                algolia {
                    applicationID = 76JC6I1QL7
                    apiKey = 49d36f868dda64113ee3bdb7ec64514a
                    indexName = tt_content_test
                }
            }
            indexing {
                tt_content {
                    indexer = Codappix\SearchCore\Domain\Index\TcaIndexer

                    additionalWhereClause(
                        tt_content.CType NOT IN ('gridelements_pi1', 'list', 'div', 'menu', 'shortcut', 'search', 'login')
                        AND tt_content.bodytext != ''
                    )

                    mapping {
                        CType {
                            type = keyword
                        }
                    }
                }

                tx_news_domain_model_news {
                    indexer = Codappix\SearchCore\Domain\Index\TcaIndexer

                    mapping {
                        CType {
                            type = keyword
                        }
                    }

                    dataProcessing {
                        1 = Mahu\SearchAlgolia\DataProcessing\NewsProcessor
                    }
                }

                pages {
                    indexer = Codappix\SearchCore\Domain\Index\TcaIndexer\PagesIndexer
                    abstractFields = abstract, description, bodytext

                    mapping {
                        CType {
                            type = keyword
                        }
                    }
                }
            }

            searching {
                facets {
                    contentTypes {
                        field = CType
                    }
                }
            }
        }
    }
}

module.tx_searchcore < plugin.tx_searchcore
