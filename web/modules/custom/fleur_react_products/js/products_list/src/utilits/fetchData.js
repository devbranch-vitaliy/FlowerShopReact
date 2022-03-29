import React, {useEffect} from 'react';
import {setGlobalState, useGlobalState, dispatch} from "./globals";
import {attachRelationship, request} from "./api";

const FetchProducts = () => {
  const [page] = useGlobalState("page");
  const perRow = 3;
  const perPage = perRow;

  useEffect(() => {
    setGlobalState("isLoading", true);

    request('products_list', { page: page , perPage: perPage})
      .then(products => {
        let products_data = [];

        // Fill products data.
        products.data.map((product) => {
          products_data.push({
            id: product.attributes.drupal_internal__product_id,
            title: product.attributes.title,
            path: product.attributes.path.alias,
            default_variation: attachRelationship(product.relationships.default_variation, products.included),
            field_image: attachRelationship(product.relationships.field_image, products.included),
          });
        })

        // Split data to rows.
        products_data = products_data.map((e, i) => {
          return i % perRow === 0 ? products_data.slice(i, i + perRow) : null;
        }).filter(e => { return e; });

        // Store data.
        setGlobalState("isLoading", false);
        setGlobalState("pager", ((page + 1) * perPage < Number(products.meta.count)));
        dispatch({type: "addProducts", products: products_data});
      })
      .catch(err => {
        setGlobalState("isLoading", false);
        if (err.name === 'AbortError') {
          console.log('fetch aborted')
        } else {
          console.log(err.message);
        }
      })
  }, [page]);
}

export default FetchProducts;
