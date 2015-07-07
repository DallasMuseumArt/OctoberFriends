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