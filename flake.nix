{
  description = "Darkwood production build environment";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
  };

  outputs = { nixpkgs, ... }:
    let
      systems = [
        "aarch64-darwin"
        "aarch64-linux"
        "x86_64-darwin"
        "x86_64-linux"
      ];
      forAllSystems = nixpkgs.lib.genAttrs systems;
    in
    {
      devShells = forAllSystems (system:
        let
          pkgs = import nixpkgs { inherit system; };
          php = pkgs.php85.buildEnv {
            extensions = { enabled, all }:
              pkgs.lib.unique (enabled ++ [
                all.apcu
                all.bcmath
                all.gd
                all.intl
                all.mbstring
                all.pcntl
                all.pdo_pgsql
                all.pgsql
                all.sockets
                all.sodium
                all.zip
              ]);
            extraConfig = ''
              apc.enable_cli = 1
              memory_limit = -1
            '';
          };
          nodejs = pkgs.nodejs_22;
        in
        {
          default = pkgs.mkShellNoCC {
            name = "darkwood-build";
            packages = [
              php
              php.packages.composer
              nodejs
            ];

            COMPOSER_MEMORY_LIMIT = "-1";
          };
        });

      formatter = forAllSystems (system: nixpkgs.legacyPackages.${system}.nixfmt-rfc-style);
    };
}
