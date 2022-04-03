import React from 'react';
import Modal from 'react-modal';
import {dispatch, useGlobalState} from "../utilits/globals";

const ModalCart = () => {
  const [modal_cart] = useGlobalState('modal_cart');

  const customStyles = {
    content: {
      top: '50%',
      left: '50%',
      right: 'auto',
      bottom: 'auto',
      marginRight: '-50%',
      transform: 'translate(-50%, -50%)',
    },
  };

  function closeModal() {
    dispatch({type: 'hideCart'});
  }

  return (
    <Modal
      isOpen={modal_cart.show}
      onRequestClose={closeModal}
      style={customStyles}
      ariaHideApp={false}
    >
      {modal_cart.product &&
        <h2>{modal_cart.product.title}</h2>
      }
      <button onClick={closeModal}>close</button>
    </Modal>
  )
};

export default ModalCart;
