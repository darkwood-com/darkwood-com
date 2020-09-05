/**
 * @author Mathieu Ledru
 */

Choc3D.Run = function(container) {

    THREE.EventDispatcher.call( this );

    var me = this;

    this.container = container;

    this.stats = new Stats();
    this.stats.domElement.style.position = 'absolute';
    this.stats.domElement.style.left = '0px';
    this.stats.domElement.style.top = '0px';
    this.container.appendChild(this.stats.domElement);

    this.choc3d = new Choc3D.App();
    this.container.appendChild(this.choc3d.renderer.domElement);

    this.control = new THREE.OrbitControls(this.choc3d.camera);
    this.control.userZoom = false;
    this.control.autoRotate = true;
    this.control.minDistance = 1;
    this.control.maxDistance = 4;
    this.control.addEventListener('change', function() {
        me.choc3d.light.position.copy(me.choc3d.camera.position);
    });

    this.data = {
        rotation: '',
        zoom: '',
        material: function() {},
        background: function() {

            var objects = {
                'none': function() {
                    return null;
                },
                'sky': function() {
                    var skyGeo = new THREE.SphereGeometry( 4000, 32, 15 );
                    var skyMat = new THREE.ShaderMaterial( {
                        vertexShader: THREE.SkyShader.vertexShader,
                        fragmentShader: THREE.SkyShader.fragmentShader,
                        uniforms: THREE.SkyShader.uniforms,
                        side: THREE.BackSide
                    } );

                    return new THREE.Mesh( skyGeo, skyMat );
                }
            };

            var background = null;
            var backgrounds = {};
            for(var name in objects) {
                backgrounds[name] = (function(name) {
                    return function() {
                        if(background) me.choc3d.scene.remove(background);
                        background = objects[name]();
                        if(background) me.choc3d.scene.add(background);
                    };
                })(name);
            }

            return backgrounds;
        },
        ground: function() {

            var objects = {
                'none': function() {
                    return null;
                },
                'earth': function() {
                    var groundGeo = new THREE.PlaneGeometry( 10000, 10000 );
                    var groundMat = new THREE.MeshPhongMaterial( { ambient: 0xffffff, color: 0xffffff, specular: 0x050505 } );
                    groundMat.color.setHSV( 0.095, 0.5, 1 );

                    var ground = new THREE.Mesh( groundGeo, groundMat );
                    ground.rotation.x = -Math.PI/2;
                    ground.position.y = -0.75;

                    return ground;
                }
            };

            var ground = null;
            var grounds = {};
            for(var name in objects) {
                grounds[name] = (function(name) {
                    return function() {
                        if(ground) me.choc3d.scene.remove(ground);
                        ground = objects[name]();
                        if(ground) me.choc3d.scene.add(ground);
                    };
                })(name);
            }

            return grounds;
        },
        lights: function() {

            var vars = {
                'disco': {
                    'lights':[]
                }
            };

            var objects = {
                'default': function() {
                    return [];
                },
                'directional': function() {
                    var directional = new THREE.DirectionalLight( 0xffffff, 0.5 );
                    directional.position.set( -1, 1.75, 1 );
                    directional.position.multiplyScalar( 50 );

                    return [directional];
                },
                'disco': function() {
                    vars.disco.lights.splice(0, vars.disco.lights.length);

                    var lightList = [];
                    var c = new THREE.Vector3();
                    me.choc3d.scene.traverse(function(object) {
                        if( object instanceof Choc3D.Ball ) {
                            var light = new THREE.PointLight( 0xffffff, 2.0, 0.5 );

                            c.set( Math.random(), Math.random(), Math.random() ).normalize();
                            light.color.setRGB( c.x, c.y, c.z );
                            object.traverse(function(mesh) {
                                if( mesh instanceof THREE.Mesh && mesh.material.color instanceof THREE.Color ) {
                                    light.color.copy(mesh.material.color);
                                }
                            });

                            light.position.copy(object.position);

                            vars.disco.lights.push({light: light, ball: object});

                            lightList.push(light);
                        }
                    });
                    return lightList;
                }
            };

            var update = {
                'default': function() {
                },
                'directional': function() {
                },
                'disco': function() {
                    var l = vars.disco.lights.length;
                    for(var i = 0; i < l; i++) {
                        vars.disco.lights[i]['light'].position.copy(vars.disco.lights[i]['ball'].position);
                    }
                }
            };

            var lightList = [];
            var lightState = null;
            var lights = {};
            for(var name in objects) {
                lights[name] = (function(name) {
                    return function() {
                        var i;

                        for(i = 0; i < lightList.length; i++) {
                            me.choc3d.scene.remove(lightList[i]);
                        }
                        if(lightState) {
                            me.removeEventListener('update', update[lightState]);
                        }

                        lightList = objects[name]();

                        for(i = 0; i < lightList.length; i++) {
                            me.choc3d.scene.add(lightList[i]);
                        }
                        me.addEventListener('update', update[name]);

                        //update materials
                        me.choc3d.scene.traverse(function(object) {
                            if( object instanceof THREE.Mesh ) {
                                object.material.needsUpdate = true;
                            }
                        });

                        lightState = name;
                    };
                })(name);
            }

            return lights;
        },
        particles: function() {
            //load particleList into the scene
            var particlesLength = 2000;

            var particleList = new THREE.Geometry();

            var Pool = {
                __pools: [],
                pop: function() {
                    if ( this.__pools.length > 0 ) {
                        return this.__pools.pop();
                    }

                    console.log( "pool ran out!" )
                    return null;
                },
                push: function( v ) {
                    this.__pools.push( v );
                },
                list: function() {
                    return this.__pools;
                }
            };

            for ( i = 0; i < particlesLength; i ++ ) {
                particleList.vertices.push( new THREE.Vector3( Number.POSITIVE_INFINITY, Number.POSITIVE_INFINITY, Number.POSITIVE_INFINITY ) );
                Pool.push( i );
            }

            var sprite = generateSprite() ;

            function generateSprite() {

                var canvas = document.createElement( 'canvas' );
                canvas.width = 128;
                canvas.height = 128;

                var context = canvas.getContext( '2d' );

                context.beginPath();
                context.arc( 64, 64, 60, 0, Math.PI * 2, false) ;
                context.closePath();

                context.lineWidth = 0.5;
                context.stroke();
                context.restore();

                var gradient = context.createRadialGradient( canvas.width / 2, canvas.height / 2, 0, canvas.width / 2, canvas.height / 2, canvas.width / 2 );

                gradient.addColorStop( 0, 'rgba(255,255,255,1)' );
                gradient.addColorStop( 0.2, 'rgba(255,255,255,1)' );
                gradient.addColorStop( 0.4, 'rgba(200,200,200,1)' );
                gradient.addColorStop( 1, 'rgba(0,0,0,1)' );

                context.fillStyle = gradient;

                context.fill();

                return canvas;

            }

            var texture = new THREE.Texture( sprite );
            texture.needsUpdate = true;

            var material = new THREE.ParticleBasicMaterial({
                map: texture,
                depthWrite:		false,
                transparent:	true
            });

            var particleCloud = new THREE.ParticleSystem( particleList, material );

            me.choc3d.scene.add(particleCloud);

            var objects = {
                'none': function() {
                    return [];
                },
                'line': function() {
                    var sparksEmitterList = [];
                    me.choc3d.scene.traverse(function(object) {
                        if( object instanceof Choc3D.Ball ) {
                            var ball = object;

                            var counter = new SPARKS.SteadyCounter( 50 );
                            var sparksEmitter = new SPARKS.Emitter( counter );

                            sparksEmitter.addInitializer( new SPARKS.Position( new SPARKS.PointZone( ball.position ) ) );
                            sparksEmitter.addInitializer( new SPARKS.Lifetime( 0.5 ));
                            sparksEmitter.addInitializer( new SPARKS.Target( null, function() {
                                // pop an available particle from pool
                                var target = Pool.pop();
                                return target;
                            }));
                            //sparksEmitter.addInitializer( new SPARKS.Velocity( new SPARKS.PointZone( new THREE.Vector3( 0, -5, 1 ) ) ) );

                            sparksEmitter.addAction( new SPARKS.Age() );
                            //sparksEmitter.addAction( new SPARKS.Accelerate( 0, 0.5, 0.5 ) );
                            sparksEmitter.addAction( new SPARKS.Move() );
                            //sparksEmitter.addAction( new SPARKS.RandomDrift( 1, 1, 1) );


                            sparksEmitter.addCallback( SPARKS.EVENT_PARTICLE_CREATED , function( p ) {
                                p.target.position = p.position;
                                var target = p.target;

                                if ( target ) {
                                    particleList.vertices[ target ] = p.position;
                                }
                            });
                            sparksEmitter.addCallback( SPARKS.EVENT_PARTICLE_DEAD , function( p ) {
                                var target = p.target;

                                if ( target ) {
                                    // Hide the particle
                                    particleList.vertices[ target ].set( Number.POSITIVE_INFINITY, Number.POSITIVE_INFINITY, Number.POSITIVE_INFINITY );

                                    // Mark particle system as available by returning to pool
                                    Pool.push( p.target );
                                }
                            });

                            sparksEmitterList.push(sparksEmitter);
                        }
                    });
                    return sparksEmitterList;
                }
            };

            //emitter list
            var sparksEmitterList = [];
            me.addEventListener('update', function(args) {
                var dt = args.dt / 1000;
                for(var i = 0; i < sparksEmitterList.length; i++) {
                    sparksEmitterList[i].update(dt);
                }
            });

            me.addEventListener('update', function(args) {
                particleCloud.geometry.verticesNeedUpdate = true;
            });

            var particles = {};
            for(var name in objects) {
                particles[name] = (function(name) {
                    return function() {
                        for(var i = 0; i < sparksEmitterList.length; i++) {
                            for(var j = 0; j < sparksEmitterList[i]._particles.length; j++) {
                                sparksEmitterList[i].dispatchEvent( SPARKS.EVENT_PARTICLE_DEAD, sparksEmitterList[i]._particles[j]);
                            }
                        }
                        sparksEmitterList = objects[name]();
                    };
                })(name);
            }

            return particles;
        }
    };

    var gui = new dat.GUI({ autoPlace: false }), folder, name, property;
    folder = gui.addFolder('Scene');
    folder.add(this.data, 'rotation').listen();
    //folder.add(this.data, 'zoom').listen();
    //folder.open();
    /*folder = gui.addFolder('Material');
    folder.add(this.data, 'material');
    folder.open();*/
    folder = gui.addFolder('Background');
    var backgrounds = this.data.background();
    for(name in backgrounds) {
        property = 'background_' + name;
        this.data[property] = backgrounds[name];
        folder.add(this.data, property).name(name);
    }
    //folder.open();
    folder = gui.addFolder('Grounds');
    var grounds = this.data.ground();
    for(name in grounds) {
        property = 'ground_' + name;
        this.data[property] = grounds[name];
        folder.add(this.data, property).name(name);
    }
    //folder.open();
    folder = gui.addFolder('Lights');
    var lights = this.data.lights();
    for(name in lights) {
        property = 'light_' + name;
        this.data[property] = lights[name];
        folder.add(this.data, property).name(name);
    }
    //folder.open();
    /*folder = gui.addFolder('Particles');
    var particles = this.data.particles();
    for(name in particles) {
        property = 'particle_' + name;
        this.data[property] = particles[name];
        folder.add(this.data, property).name(name);
    }
    folder.open();*/

    gui.domElement.style.position = 'absolute';
    gui.domElement.style.right = '0px';
    gui.domElement.style.top = '0px';
    this.container.appendChild(gui.domElement);

    this.reshape();
};

Choc3D.Run.prototype = Object.create( THREE.EventDispatcher.prototype );

Choc3D.Run.prototype.animate = function (dt) {

    this.update(dt);
    this.render();

};

Choc3D.Run.prototype.update = function (dt) {
    var zoom = new THREE.Vector3();
    zoom.sub(this.control.center, this.choc3d.camera.position);
    this.data.rotation = this.sprintf(this.choc3d.camera.rotation);
    this.data.zoom = this.sprintf(zoom.length());

    this.stats.update();
    this.control.update();
    this.choc3d.update(dt);

    this.dispatchEvent( {type: 'update', dt: dt} );
};

Choc3D.Run.prototype.render = function () {
    this.choc3d.render();

    this.dispatchEvent( {type: 'render'} );
};

Choc3D.Run.prototype.reshape = function () {
    var width = this.container.offsetWidth;
    var height = this.container.offsetHeight;

    this.choc3d.reshape(width, height);
};

Choc3D.Run.prototype.sprintf = function(data) {
    function round(num, dec) {
        return Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
    }

    if( data instanceof THREE.Vector3 ) {
        return '( ' + round(data.x, 2) + ', ' + round(data.y, 2) + ', ' + round(data.z, 2) + ' )';
    } else if ((typeof(data) === 'number' || typeof(data) === 'string') && data !== '' && !isNaN(data)) {
        return round(data, 2);
    }

    return data;
};