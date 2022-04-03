import React, {useEffect, useRef} from 'react';
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
  const [filters_values] = useGlobalState("filters_values");
  const prevValue = useRef({filters_values}).current;
  const perRow = 3;
  const perPage = perRow * 2;

  useEffect(() => {
    setGlobalState("isLoading", true);

    request('products_list', { page: page , perPage: perPage, filters: filters_values})
      .then(products => {
        let products_data = [];

        // Fill products data.
        products.data.map((product) => {
          products_data.push({
            id: product.attributes.drupal_internal__product_id,
            title: product.attributes.title,
            path: product.attributes.path.alias,
            colors: product.attributes.field_colors,
            variations: product.relationships.variations.data,
            default_variation: product.relationships.default_variation.data,
            field_image: product.relationships.field_image.data,
          });
        })

        // Split data to rows.
        products_data = products_data.map((e, i) => {
          return i % perRow === 0 ? products_data.slice(i, i + perRow) : null;
        }).filter(e => { return e; });

        // Store data.
        setGlobalState("isLoading", false);
        setGlobalState("pager", ((page + 1) * perPage < Number(products.meta.count)));
        if (prevValue.filters_values === filters_values) {
          dispatch({type: "addIncludes", includes: products.included});
          dispatch({type: "addProducts", products: products_data});
        }
        else {
          setGlobalState("includes", products.included);
          setGlobalState("products", products_data);
        }

        // Updated prev value.
        prevValue.filters_values = filters_values;
      })
      .catch(err => {
        setGlobalState("isLoading", false);
        console.log(err.message);
      })
  }, [page, filters_values]);
}

export {FetchProducts, FetchFilters};
