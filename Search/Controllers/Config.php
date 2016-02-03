<?php
namespace Modules\Search\Controllers;
/**
 * Class Config
 */
class Config
{
    /**
     * @var array
     */
    private static $configs = array(
        // ***** Database ***** //
        'dataSources'           => array(
            'ls_query' => array(
                'table'              => 'cj',
                // specify the name of search columns
                'searchColumns'      => array('nombre'),
                // specify order by column. This is optional
                'orderBy'            => '',
                // specify order direction e.g. ASC or DESC. This is optional
                'orderDirection'     => '',
                // filter the result by entering table column names
                // to get all the columns, remove filterResult or make it an empty array
                'filterResult'       => array('nombre'),
                // specify search query comparison operator. possible values for comparison operators are: 'LIKE' and '='. this is required.
                'comparisonOperator' => 'LIKE',
                // searchPattern is used to specify how the query is searched. possible values are: 'q', '*q', 'q*', '*q*'. this is required.
                'searchPattern'      => 'q*',
                // specify search query case sensitivity
                'caseSensitive'      => false,
                // to limit the maximum number of result uncomment this:
                //'maxResult' => 100,
                // to display column header, change 'active' value to true
                'displayHeader' => array(
                    'active' => false,
                    'mapper' => array(
//                        'your_first_column' => 'Your Desired Title',
//                        'your_second_column' => 'Your Desired Second Title'
                    )
                )
            )
        ),
        // ***** Form ***** //
        'antiBot'               => "ajaxlivesearch_guard",
        // Assigning more than 3 seconds is not recommended
        'searchStartTimeOffset' => 2,
        // ***** Search Input ***** /
        'maxInputLength'        => 20,
        'accessControlAllowOrigin'=> 'http://localhost' // put your site here to allow access
    );

    /**
     *
     * @param  $key
     * @throws \Exception
     * @return mixed
     */
    public static function getConfig($key)
    {
        if (!array_key_exists($key, static::$configs)) {
            throw new \Exception("Key: {$key} does not exist in the configs");
        }

        return static::$configs[$key];
    }
}
