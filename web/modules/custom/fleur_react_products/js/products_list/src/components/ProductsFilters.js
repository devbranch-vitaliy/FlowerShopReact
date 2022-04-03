import React from 'react';
import {dispatch, useGlobalState} from "../utilits/globals";

const ProductsFilters = () => {
  const [filters] = useGlobalState("filters");
  const [filters_values] = useGlobalState("filters_values");
  return (
    <div className="products-filters">
      <div className="filters-label" key={"label"}>Filter by:</div>
      {filters && Object.keys(filters).map((filter_name) => (
        <div className={"select-wrapper"} key={filter_name}>
          <select
            defaultValue={filters_values[filter_name]}
            onChange={(e) => dispatch({type: "filterUpdate", filter: {name: filter_name, value: e.target.value}})}
          >
            {filters[filter_name].map((filter_data) => {
                return (
                  <option value={filter_data.value} key={filter_data.value}>{filter_data.name}</option>
                )
              }
            )}
          </select>
        </div>
      ))}
    </div>
  )
};

export default ProductsFilters;
