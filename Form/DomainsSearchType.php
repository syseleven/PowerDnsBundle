<?php
/**
 * This file is part of the SysEleven PowerDnsBundle.
 *
 * (c) SysEleven GmbH <http://www.syseleven.de/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author   M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Form
 */
namespace SysEleven\PowerDnsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the base form for searching in the domains table
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Form
 */
class DomainsSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search','text', array('required' => false))
            ->add('name', 'text', array('required' => false))
            ->add('account','text', array('required' => false))
            ->add('type','choice', array('choices' => array('MASTER' => 'MASTER', 'NATIVE' => 'NATIVE', 'SLAVE' => 'SLAVE', 'SUPERSLAVE' => 'SUPERSLAVE'),'multiple' => true))
            ->add('master','text', array('required' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'data_class' => 'SysEleven\PowerDnsBundle\Query\DomainsQuery',
            ));
    }


    public function getName()
    {
        return '';
    }

}
 