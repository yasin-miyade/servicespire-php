import React from "react";
import "./Home.css";
import { Link } from "react-router-dom";
import Header from "../Component/Header.jsx";
import Footer from "../Component/Footer.jsx";
import Contactimg from "../assets/Contact.svg";
// import Aboutimg from "../assets/About.svg";
import Web from '../assets/Web.jpg';

import { Card, CardContent } from "../components/ui/card";
import { ShieldCheck, MessageSquare, Users, TrendingUp, CheckCircle, DollarSign } from "lucide-react";


function Home() {

  const services = [
    {
      icon: <ShieldCheck size={40} className="text-blue-600" />,
      title: "Secure Payments",
      description:
        "We understand the importance of security, which is why we use SSL encryption and trusted payment gateways like PayPal and Stripe to ensure your transactions are safe and hassle-free."
    },
    {
      icon: <MessageSquare size={40} className="text-green-600" />,
      title: "Communication",
      description:
        "We believe in transparency and clarity in all our transactions, ensuring that you're always informed."
    },
    {
      icon: <CheckCircle size={40} className="text-purple-600" />,
      title: "Accountability",
      description:
        "We take full responsibility for our actions, always striving to do what’s right for our clients."
    }
  ];


  const FirstCurve = () => (
    <path
      d="M30 40 Q100 120, 170 140"
      stroke="#6A00D4"
      strokeWidth="4"
      fill="none"
      strokeLinecap="round"
    />
  );
  
  // Second Curve Component (Lower Position)
  const SecondCurve = () => (
    <path
      d="M35 70 Q120 160, 220 170"
      stroke="#6A00D4"
      strokeWidth="4"
      fill="none"
      strokeLinecap="round"
    />
  );
  
  // Dots Component (Matching New Positions)
  const Dots = () => (
    <>
      <circle cx="170" cy="140" r="6" fill="#6A00D4" />
      <circle cx="218" cy="170" r="6" fill="#6A00D4" />
    </>
  );

  return (
    <>
      <Header />

      <section>
        <div className=" flex justify-between items-center  h-screen  ">
          <div className="flex justify-center flex-col pl-20 -mt-10 gap-5">
            <p className="text-purple-900 font-bold text-2xl">We Make IT Possible</p>
            <h1 className="text-7xl font-bold  text-gray-900">
              Service with a Purpose,<br/> Help with a Heart
            </h1>
            <p className="text-xl mt-5">Your needs, our priority delivered with care</p>
            <Link
              to="about"
              className="text-white bg-purple-600 hover:bg-purple-800 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg w-30 text-sm px-4 lg:px-5 py-2 lg:py-2.5 focus:outline-none mt-5"
            >
              Learn More
            </Link>

            
          </div>

          <div className="flex justify-center mt-35 ml-1">
            <img src={Web} alt="" className="w-150 h-150" />
          </div>
          
        </div>
        <div className=" -mt-50 ml-110">
      <svg
      viewBox="0 0 200 200"
      xmlns="http://www.w3.org/2000/svg"
      className="w-50 h-40"
    >
      <FirstCurve />
      <SecondCurve />
      <Dots />
    </svg>
      </div>
         
      </section>

      {/* <section className="bg-gray-100 flex flex-col items-center justify-center py-20">
        <div className="conten flex justify-center">
          <div className="flex flex-col items-center justify-center mr-10">
            <h2 className="text-4xl font-semibold text-center">Our Services</h2>
            <p className="text-lg text-center mt-4">
              We provide a variety of services to help you with your needs.{" "}
              <br />
              Our services are designed to help you with your daily tasks and
              make your life easier.
            </p>
            <Link
              to="/services"
              className="text-white bg-purple-600 hover:bg-purple-800 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 focus:outline-none mt-8"
            >
              Learn More
            </Link>
          </div>
          <img
            src={Aboutimg}
            alt=""
            className="w-xl box-border shadow-2xl bg-white rounded-2xl hover:scale-105 transition-all duration-900 ease-in-out"
          />
        </div>
      </section> */}


<div className="container mx-auto py-10 px-4 text-center mt-30">
      <h2 className="text-4xl font-bold mb-20 text-gray-800">Our <span className="text-purple-800">Services</span></h2>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {services.map((service, index) => (
          <Card key={index} className="p-6 shadow-lg rounded-2xl transition-transform transform hover:-translate-y-2 hover:shadow-2xl">
            <div className="flex flex-col items-center text-center space-y-4">
              {service.icon}
              <h3 className="text-xl font-semibold text-gray-700">{service.title}</h3>
              <p className="text-gray-600">{service.description}</p>
            </div>
          </Card>
        ))}
      </div>
      <div className="mt-15">
      <Link
              to="/services"
              className="text-white bg-purple-600 hover:bg-purple-800 mt-30 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 focus:outline-none"
            >
              Learn More
            </Link>
      </div>
      
    </div>


      <section className=" flex flex-col items-center justify-center py-20">
        <div className="content flex justify-center ">
          <img
            src={Contactimg}
            alt=""
            className="w-xl  h-fit shadow-2xl box-border rounded-2xl hover:scale-105 transition-all duration-900 ease-in-out"
          />
          <div className="flex flex-col items-center justify-center ml-10">
            <h2 className="text-4xl font-semibold text-center">Contact Us</h2>
            <p className="text-lg text-center mt-4">
              If you have any questions or need help, feel free to contact us.{" "}
              <br />
              Our team is always ready to help you with your needs.
            </p>
            <Link
              to="contact"
              className="text-white bg-purple-600 hover:bg-purple-800 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 focus:outline-none mt-8"
            >
              Contact Us
            </Link>
          </div>
        </div>
      </section>
          
      

      <Footer />
    </>
  );
}

export default Home;