import React, {useEffect} from 'react';
import {setGlobalState, useGlobalState, dispatch} from "./globals";
import {attachRelationship, request} from "./api";

const FetchFilters = () => {
  const [filters, setFilters] = useGlobalState('filters');
  useEffect(() => {
    for (const filter_name in filters) {
      if (filter_name === 'colors') {
        continue;
      }
      request('taxonomy_term', { name: filter_name})
        .then(terms => {
          terms.data.map((term) => {
            filters[filter_name].push({
              value: term.id,
              name: term.attributes.name
            });
          })
          setFilters(filters);
        })
        .catch(err => {
          setGlobalState("isLoading", false);
          console.log(err.message);
        })
    }
  }, [])
}

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
        console.log(err.message);
      })
  }, [page]);
}

export {FetchProducts, FetchFilters};
