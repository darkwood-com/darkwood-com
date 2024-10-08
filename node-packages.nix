# This file has been generated by node2nix 1.11.1. Do not edit!

{nodeEnv, fetchurl, fetchgit, nix-gitignore, stdenv, lib, globalBuildInputs ? []}:

let
  sources = {
    "@popperjs/core-2.11.8" = {
      name = "_at_popperjs_slash_core";
      packageName = "@popperjs/core";
      version = "2.11.8";
      src = fetchurl {
        url = "https://registry.npmjs.org/@popperjs/core/-/core-2.11.8.tgz";
        sha512 = "P1st0aksCrn9sGZhp8GMYwBnQsbvAWsZAX44oXNNvLHGqAOcoVxmjZiohstwQ7SqKnbR47akdNi+uleWD8+g6A==";
      };
    };
    "bootstrap-5.3.2" = {
      name = "bootstrap";
      packageName = "bootstrap";
      version = "5.3.2";
      src = fetchurl {
        url = "https://registry.npmjs.org/bootstrap/-/bootstrap-5.3.2.tgz";
        sha512 = "D32nmNWiQHo94BKHLmOrdjlL05q1c8oxbtBphQFb9Z5to6eGRDCm0QgeaZ4zFBHzfg2++rqa2JkqCcxDy0sH0g==";
      };
    };
    "jquery-3.7.1" = {
      name = "jquery";
      packageName = "jquery";
      version = "3.7.1";
      src = fetchurl {
        url = "https://registry.npmjs.org/jquery/-/jquery-3.7.1.tgz";
        sha512 = "m4avr8yL8kmFN8psrbFFFmB/If14iN5o9nw/NgnnM+kybDJpRsAynV2BsfpTYrTRysYUdADVD7CkUUizgkpLfg==";
      };
    };
    "popper.js-1.16.1" = {
      name = "popper.js";
      packageName = "popper.js";
      version = "1.16.1";
      src = fetchurl {
        url = "https://registry.npmjs.org/popper.js/-/popper.js-1.16.1.tgz";
        sha512 = "Wb4p1J4zyFTbM+u6WuO4XstYx4Ky9Cewe4DWrel7B0w6VVICvPwdOpotjzcf6eD8TsckVnIMNONQyPIUFOUbCQ==";
      };
    };
  };
  args = {
    name = "_at_darkwood-com_slash_darkwood-com";
    packageName = "@darkwood-com/darkwood-com";
    version = "1.0.0";
    src = ./.;
    dependencies = [
      sources."@popperjs/core-2.11.8"
      sources."bootstrap-5.3.2"
      sources."jquery-3.7.1"
      sources."popper.js-1.16.1"
    ];
    buildInputs = globalBuildInputs;
    meta = {
      description = "Darkwood website";
      homepage = "https://github.com/darkwood-com/darkwood-com#readme";
      license = "MIT";
    };
    production = true;
    bypassCache = true;
    reconstructLock = true;
  };
in
{
  args = args;
  sources = sources;
  tarball = nodeEnv.buildNodeSourceDist args;
  package = nodeEnv.buildNodePackage args;
  shell = nodeEnv.buildNodeShell args;
  nodeDependencies = nodeEnv.buildNodeDependencies (lib.overrideExisting args {
    src = stdenv.mkDerivation {
      name = args.name + "-package-json";
      src = nix-gitignore.gitignoreSourcePure [
        "*"
        "!package.json"
        "!package-lock.json"
      ] args.src;
      dontBuild = true;
      installPhase = "mkdir -p $out; cp -r ./* $out;";
    };
  });
}
