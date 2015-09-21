<?php
/**
 * @SWG\Swagger(
 *     basePath="/friends/api/",
 *     schemes={"http"},
 *     produces={"application/json"},
 *     consumes={"application/json", "application/x-www-form-urlencoded"},
 *     @SWG\Info(
 *         version="2.5.1",
 *         title="DMA Friends",
 *         description="A platform for users to earn badges and redeem rewards",
 *         @SWG\License(
 *              name="Apache 2.0",
 *              url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *         )
 *     )
 * )
 */

 
/**
 *     @SWG\Definition(
 *         definition="error404",
 *         required={"code", "http_code", "message"},
 *         @SWG\Property(
 *             property="code",
 *             type="string",
 *             default="GEN-NOT-FOUND"
 *         ),
 *         @SWG\Property(
 *             property="http_code",
 *             type="integer",
 *             format="int32",
 *             default=404
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string",
 *             default="Resource Not Found"
 *         )
 *     )
 *     
 *     @SWG\Definition(
 *         definition="UserError404",
 *         required={"code", "http_code", "message"},
 *         @SWG\Property(
 *             property="code",
 *             type="string",
 *             default="GEN-NOT-FOUND"
 *         ),
 *         @SWG\Property(
 *             property="http_code",
 *             type="integer",
 *             format="int32",
 *             default=404
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string",
 *             default="User not found"
 *         )
 *     )
 *     
 *     
 *     @SWG\Definition(
 *         definition="error500",
 *         required={"code", "http_code", "message"},
 *         @SWG\Property(
 *             property="code",
 *             type="string",
 *             default="GEN-INTERNAL-ERROR"
 *         ),
 *         @SWG\Property(
 *             property="http_code",
 *             type="integer",
 *             format="int32",
 *             default=500
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string"
 *         )
 *     )
 */
 
// TODO : Swagger don't support yet dynamic query parameters
// see https://github.com/swagger-api/swagger-spec/issues/367

/**
 * // Common parameters
 * 
 * @SWG\Parameter(
 *      description="Control the number of items per page. When set as zero all items are return witout pagination.",
 *      name="per_page",
 *      in="query",
 *      type="integer",
 *      format="int32",
 *      default="50",
 *      required=false
 * )
 * 
 * @SWG\Parameter(
 *      description="Page to display",
 *      name="page",
 *      in="query",
 *      type="integer",
 *      format="int32",
 *      default="1",
 *      required=false
 * )
 * 
 * @SWG\Parameter(
 *      description="Sort by this field. for DESC order prepend '-'. eg. -id",
 *      name="sort",
 *      in="query",
 *      type="array",
 *      required=false,
 *      items=@SWG\Schema(type="string"),
 *      collectionFormat="csv"
 * ) 
 *
 */